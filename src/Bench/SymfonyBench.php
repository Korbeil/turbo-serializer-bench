<?php

namespace Korbeil\TurboSerializerBench\Bench;

use Korbeil\TurboSerializerBench\Person;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Korbeil\TurboSerializerBench\AbstractBench;

final class SymfonyBench extends AbstractBench
{
    private SerializerInterface $serializer;

    public function bootstrap(): void
    {
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
        ], [new JsonEncoder()]);
    }

    protected function doBenchSerialize(): string
    {
        return $this->serializer->serialize($this->toSerialize, 'json');
    }

    protected function doBenchDeserialize(): array
    {
        return $this->serializer->deserialize($this->toDeserialize, sprintf('%s[]', Person::class), 'json');
    }

    public function getName(): string
    {
        return 'Symfony';
    }

    public function getPackageName(): string
    {
        return 'symfony/serializer';
    }
}
