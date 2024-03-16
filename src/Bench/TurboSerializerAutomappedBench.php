<?php

namespace Korbeil\TurboSerializerBench\Bench;

use AutoMapper\AutoMapper;
use Korbeil\TurboSerializerBench\AbstractBench;
use Korbeil\TurboSerializerBench\Person;
use Mtarld\JsonEncoderBundle\JsonDecoder;
use Mtarld\JsonEncoderBundle\JsonEncoder;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeResolver\StringTypeResolver;
use TurboSerializer\Serializer;

final class TurboSerializerAutomappedBench extends AbstractBench
{
    private Serializer $serializer;

    public function bootstrap(): void
    {
        $cacheDir = $this->cacheDir . '/json_encoder/turbo_encoder';

        $this->serializer = new Serializer(
            JsonEncoder::create($cacheDir),
            JsonDecoder::create($cacheDir),
            AutoMapper::create(),
            new StringTypeResolver(),
        );
    }

    protected function doBenchSerialize(): string
    {
        $type = Type::list(Type::object(Person::class));

        return $this->serializer->serialize($this->toSerialize, 'json', [
            Serializer::TYPE => $type,
            Serializer::NORMALIZED_TYPE => $type,
        ]);
    }

    protected function doBenchDeserialize(): array
    {
        $type = Type::list(Type::object(Person::class));

        return $this->serializer->deserialize($this->toDeserialize, (string) $type, 'json', [
            Serializer::NORMALIZED_TYPE => $type,
        ]);
    }

    public function getName(): string
    {
        return 'TurboSerializer automapped';
    }

    public function getPackageName(): string
    {
        return 'korbeil/turbo-serializer';
    }
}
