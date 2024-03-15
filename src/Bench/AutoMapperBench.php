<?php

namespace Korbeil\TurboSerializerBench\Bench;

use AutoMapper\AutoMapper;
use Korbeil\TurboSerializerBench\AbstractBench;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class AutoMapperBench extends AbstractBench
{
    private AutoMapper $autoMapper;
    private JsonEncoder $encoder;

    public function bootstrap(): void
    {
        $this->autoMapper = AutoMapper::create();
        $this->encoder = new JsonEncoder();
    }

    protected function doBenchSerialize(array $objects): void
    {
        $array = [];
        foreach ($objects as $object) {
            $array[] = $this->autoMapper->map($object, 'array');
        }
        $this->encoder->encode($array, 'json');
    }

    protected function doBenchDeserialize(string $content, string $type): void
    {
        $decoded = $this->encoder->decode($content, 'json');
        $this->autoMapper->map($decoded, sprintf('%s[]', $type));
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