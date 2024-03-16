<?php

namespace Korbeil\TurboSerializerBench\Bench;

use AutoMapper\AutoMapper;
use Korbeil\TurboSerializerBench\AbstractBench;
use Korbeil\TurboSerializerBench\Person;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

final class AutoMapperBench extends AbstractBench
{
    private AutoMapper $autoMapper;
    private JsonEncoder $encoder;

    public function bootstrap(): void
    {
        $this->autoMapper = AutoMapper::create();
        $this->encoder = new JsonEncoder();
    }


    protected function doBenchSerialize(): string
    {
        return $this->encoder->encode(array_map(fn (Person $p): array => $this->autoMapper->map($p, 'array'), $this->toSerialize), 'json');
    }

    protected function doBenchDeserialize(): array
    {
        $decoded = $this->encoder->decode($this->toDeserialize, 'json');
        $denormalized = [];

        foreach ($decoded as $item) {
            $denormalized[] = $this->autoMapper->map($item, Person::class);
        }

        return $denormalized;
    }

    public function getName(): string
    {
        return 'AutoMapper';
    }

    public function getPackageName(): string
    {
        return 'jane-php/automapper';
    }
}
