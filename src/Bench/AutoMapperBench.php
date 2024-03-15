<?php

namespace Korbeil\TurboSerializerBench\Bench;

use AutoMapper\AutoMapper;
use Korbeil\TurboSerializerBench\AbstractBench;
use Korbeil\TurboSerializerBench\Person;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class AutoMapperBench extends AbstractBench
{
    private AutoMapper $autoMapper;
    private JsonEncoder $encoder;

    private string $type;

    public function bootstrap(): void
    {
        $this->autoMapper = AutoMapper::create();
        $this->encoder = new JsonEncoder();
        $this->type = sprintf('%s[]', Person::class);
    }


    protected function doBenchSerialize(): string
    {
        return $this->encoder->encode(array_map(fn (Person $p): array => $this->autoMapper->map($p, 'array'), $this->toSerialize), 'json');
    }

    protected function doBenchDeserialize(): array
    {
        // var_dump($this->encoder->decode($this->toDeserialize, 'json'), $this->type);

        return [];
        return $this->autoMapper->map([], $this->type);
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
