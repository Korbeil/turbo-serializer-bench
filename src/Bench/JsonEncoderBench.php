<?php

namespace Korbeil\TurboSerializerBench\Bench;

use Korbeil\TurboSerializerBench\AbstractBench;
use Korbeil\TurboSerializerBench\Person;
use Mtarld\JsonEncoderBundle\DecoderInterface;
use Mtarld\JsonEncoderBundle\EncoderInterface;
use Mtarld\JsonEncoderBundle\JsonDecoder;
use Mtarld\JsonEncoderBundle\JsonEncoder;
use Mtarld\JsonEncoderBundle\Mapping\PropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\GenericTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Encode\AttributePropertyMetadataLoader as EncodeAttributePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Encode\DateTimeTypePropertyMetadataLoader as EncodeDateTimeTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Decode\AttributePropertyMetadataLoader as DecodeAttributePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Decode\DateTimeTypePropertyMetadataLoader as DecodeDateTimeTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\TypeResolver;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeContext\TypeContextFactory;
use Symfony\Component\TypeInfo\TypeResolver\StringTypeResolver;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver as TypeInfoResolver;

final class JsonEncoderBench extends AbstractBench
{
    private EncoderInterface $encoder;
    private DecoderInterface $decoder;

    public function bootstrap(): void
    {
        $encoderCacheDir = $this->cacheDir . '/json_encoder/encoder';
        $decoderCacheDir = $this->cacheDir . '/json_encoder/decoder';
        $lazyGhostCacheDir = $this->cacheDir . '/json_encoder/lazy_ghost';

        $typeContextFactory = new TypeContextFactory($stringTypeResolver = new StringTypeResolver());
        $typeResolver = new TypeResolver(TypeInfoResolver::create(), $typeContextFactory);

        $this->encoder = new JsonEncoder(new GenericTypePropertyMetadataLoader(
            new EncodeDateTimeTypePropertyMetadataLoader(new EncodeAttributePropertyMetadataLoader(
                new PropertyMetadataLoader($typeResolver),
                $typeResolver,
            )),
            $typeContextFactory,
        ), $encoderCacheDir);

        $this->decoder = new JsonDecoder(new GenericTypePropertyMetadataLoader(
            new DecodeDateTimeTypePropertyMetadataLoader(new DecodeAttributePropertyMetadataLoader(
                new PropertyMetadataLoader($typeResolver),
                $typeResolver,
            )),
            $typeContextFactory,
        ), $decoderCacheDir);
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
