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

class TurboSerializerBench extends AbstractBench
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
        return $this->serializer->serialize($this->toSerialize, 'json', [Serializer::TYPE => Type::list(Type::object(Person::class))]);
    }

    protected function doBenchDeserialize(): array
    {
        return $this->serializer->deserialize($this->toDeserialize, (string) Type::list(Type::object(Person::class)), 'json');
    }

    public function getName(): string
    {
        return 'TurboSerializer';
    }

    public function getPackageName(): string
    {
        return 'korbeil/turbo-serializer';
    }
}
