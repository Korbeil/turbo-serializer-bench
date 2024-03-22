<?php

namespace Korbeil\TurboSerializerBench\Bench;

use AutoMapper\AutoMapper;
use Korbeil\TurboSerializerBench\Person;
use Mtarld\JsonEncoderBundle\DecoderInterface;
use Mtarld\JsonEncoderBundle\EncoderInterface;
use Mtarld\JsonEncoderBundle\JsonDecoder;
use Mtarld\JsonEncoderBundle\JsonEncoder;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\OutputTimeUnit;
use PhpBench\Attributes\ParamProviders;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyJsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeResolver\StringTypeResolver;
use TurboSerializer\Serializer as TurboSerializer;

#[BeforeMethods(['initContent', 'bootstrap'])]
#[OutputTimeUnit('milliseconds', 3)]
final class Bench
{
    private array $toSerialize;
    private string $toDeserialize;

    private Filesystem $fs;

    private AutoMapper $autoMapper;
    private SymfonyJsonEncoder $symfonyEncoder;
    private EncoderInterface $encoder;
    private DecoderInterface $decoder;
    private SerializerInterface $serializer;
    private SerializerInterface $turboSerializer;
    private SerializerInterface $turboSerializerMapped;

    public function bootstrap(): void
    {
        $cacheDir = sprintf('%s/symfony_test', sys_get_temp_dir());

        $fs = new Filesystem();
        $fs->remove($cacheDir);
        $fs->mkdir($cacheDir);

        $this->autoMapper = AutoMapper::create();
        $this->symfonyEncoder = new SymfonyJsonEncoder();

        $this->encoder = JsonEncoder::create($cacheDir . '/json_encoder/encoder');
        $this->decoder = JsonDecoder::create($cacheDir . '/json_encoder/encoder');

        $this->turboSerializer = new TurboSerializer(
            JsonEncoder::create($cacheDir . '/json_encoder/turbo_encoder'),
            JsonDecoder::create($cacheDir . '/json_encoder/turbo_encoder'),
            AutoMapper::create(),
            new StringTypeResolver(),
        );

        $this->turboSerializerMapped = new TurboSerializer(
            JsonEncoder::create($cacheDir . '/json_encoder/turbo_encoder_mapped'),
            JsonDecoder::create($cacheDir . '/json_encoder/turbo_encoder_mapped'),
            AutoMapper::create(),
            new StringTypeResolver(),
        );

        $classMetadataFactory = new CacheClassMetadataFactory(new ClassMetadataFactory(new AttributeLoader()), new ArrayAdapter());

        $this->serializer = new Serializer([
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new ObjectNormalizer(
                $classMetadataFactory,
                new MetadataAwareNameConverter($classMetadataFactory),
                new PropertyAccessor(),
                new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]),
                new ClassDiscriminatorFromClassMetadata($classMetadataFactory),
            ),
        ], [$this->symfonyEncoder]);
    }

    public function initContent(): void
    {
        $this->toSerialize = [];

        for ($i = 0; $i < 200; $i++) {
            $this->toSerialize[] = new Person();
        }

        $this->toDeserialize = json_encode($this->toSerialize);
    }

    /**
     * @param array{serializer: string} $params
     */
    #[ParamProviders(['serializers'])]
    #[Iterations(50)]
    #[Groups(['serialize'])]
    public function benchSerialize($params): void
    {
        $serializer = $params['serializer'];
        $type = Type::list(Type::object(Person::class));

        if ('json_encoder' === $serializer) {
            (string) $this->encoder->encode($this->toSerialize, $type);

            return;
        }

        if ('automapper' === $serializer) {
            $this->symfonyEncoder->encode(array_map(fn (Person $p): array => $this->autoMapper->map($p, 'array'), $this->toSerialize), 'json');

            return;
        }

        if ('turbo_serializer' === $serializer) {
            $this->turboSerializer->serialize($this->toSerialize, 'json', [TurboSerializer::TYPE => $type]);

            return;
        }

        if ('turbo_serializer_mapped' === $serializer) {
            $this->turboSerializerMapped->serialize($this->toSerialize, 'json', [
                TurboSerializer::TYPE => $type,
                TurboSerializer::NORMALIZED_TYPE => $type,
            ]);

            return;
        }

        if ('serializer' === $serializer) {
            $this->serializer->serialize($this->toSerialize, 'json');

            return;
        }

        throw new \InvalidArgumentException(sprintf('Unknown "%s" serializer', $serializer));
    }

    /**
     * @param array{serializer: string} $params
     */
    #[ParamProviders(['serializers'])]
    #[Iterations(50)]
    #[Groups(['deserialize'])]
    public function benchDeserialize($params): void
    {
        $serializer = $params['serializer'];
        $type = Type::list(Type::object(Person::class));

        if ('json_encoder' === $serializer) {
            $this->decoder->decode($this->toDeserialize, $type);

            return;
        }

        if ('automapper' === $serializer) {
            $decoded = $this->symfonyEncoder->decode($this->toDeserialize, 'json');
            $denormalized = [];

            foreach ($decoded as $item) {
                $denormalized[] = $this->autoMapper->map($item, Person::class);
            }

            return;
        }

        if ('turbo_serializer' === $serializer) {
            $this->turboSerializer->deserialize($this->toDeserialize, (string) $type, 'json');

            return;
        }

        if ('turbo_serializer_mapped' === $serializer) {
            $this->turboSerializerMapped->deserialize($this->toDeserialize, (string) $type, 'json', [
                TurboSerializer::NORMALIZED_TYPE => $type,
            ]);

            return;
        }

        if ('serializer' === $serializer) {
            $this->serializer->deserialize($this->toDeserialize, sprintf('%s[]', Person::class), 'json');

            return;
        }

        throw new \InvalidArgumentException(sprintf('Unknown "%s" serializer', $serializer));
    }

    /**
     * @return iterable<string, array{serializer: string}>
     */
    public function serializers(): \Generator
    {
        yield 'json encoder' => ['serializer' => 'json_encoder'];
        yield 'automapper' => ['serializer' => 'automapper'];
        yield 'turbo_serializer' => ['serializer' => 'turbo_serializer'];
        yield 'turbo_serializer_mapped' => ['serializer' => 'turbo_serializer_mapped'];
        yield 'Serializer' => ['serializer' => 'serializer'];
    }
}
