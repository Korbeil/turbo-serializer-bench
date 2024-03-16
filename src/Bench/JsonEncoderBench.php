<?php

namespace Korbeil\TurboSerializerBench\Bench;

use Korbeil\TurboSerializerBench\AbstractBench;
use Korbeil\TurboSerializerBench\Person;
use Mtarld\JsonEncoderBundle\DecoderInterface;
use Mtarld\JsonEncoderBundle\EncoderInterface;
use Mtarld\JsonEncoderBundle\JsonDecoder;
use Mtarld\JsonEncoderBundle\JsonEncoder;
use Symfony\Component\TypeInfo\Type;

final class JsonEncoderBench extends AbstractBench
{
    private EncoderInterface $encoder;
    private DecoderInterface $decoder;

    public function bootstrap(): void
    {
        $cacheDir = $this->cacheDir . '/json_encoder/encoder';

        $this->encoder = JsonEncoder::create($cacheDir);
        $this->decoder = JsonDecoder::create($cacheDir);
    }

    protected function doBenchSerialize(): string
    {
        return (string) $this->encoder->encode($this->toSerialize, Type::list(Type::object(Person::class)));
    }

    protected function doBenchDeserialize(): array
    {
        return $this->decoder->decode($this->toDeserialize, Type::list(Type::object(Person::class)));
    }

    public function getName(): string
    {
        return 'JsonEncoder';
    }

    public function getPackageName(): string
    {
        return 'mtarld/json-encoder-bundle';
    }
}
