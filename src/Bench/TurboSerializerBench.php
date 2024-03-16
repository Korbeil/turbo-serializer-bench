<?php

namespace Korbeil\TurboSerializerBench\Bench;

use AutoMapper\AutoMapper;
use Korbeil\TurboSerializerBench\AbstractBench;
use Korbeil\TurboSerializerBench\Person;
use Mtarld\JsonEncoderBundle\JsonDecoder;
use Mtarld\JsonEncoderBundle\JsonEncoder;
use Mtarld\JsonEncoderBundle\Mapping\Encode\AttributePropertyMetadataLoader as EncodeAttributePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Encode\DateTimeTypePropertyMetadataLoader as EncodeDateTimeTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Decode\AttributePropertyMetadataLoader as DecodeAttributePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Decode\DateTimeTypePropertyMetadataLoader as DecodeDateTimeTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\GenericTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\PropertyMetadataLoader;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeContext\TypeContextFactory;
use Symfony\Component\TypeInfo\TypeResolver\StringTypeResolver;
use Mtarld\JsonEncoderBundle\Mapping\TypeResolver;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver as TypeInfoResolver;
use TurboSerializer\Serializer;

class TurboSerializerBench extends AbstractBench
{
    private Serializer $serializer;

    public function bootstrap(): void
    {
        $encoderCacheDir = $this->cacheDir . '/json_encoder/turbo_encoder';
        $decoderCacheDir = $this->cacheDir . '/json_encoder/turbo_decoder';
        $lazyGhostCacheDir = $this->cacheDir . '/json_encoder/turbo_lazy_ghost';

        $typeContextFactory = new TypeContextFactory($stringTypeResolver = new StringTypeResolver());
        $typeResolver = new TypeResolver(TypeInfoResolver::create(), $typeContextFactory);

        $jsonEncoder = new JsonEncoder(new GenericTypePropertyMetadataLoader(
            new EncodeDateTimeTypePropertyMetadataLoader(new EncodeAttributePropertyMetadataLoader(
                new PropertyMetadataLoader($typeResolver),
                $typeResolver,
            )),
            $typeContextFactory,
        ), $encoderCacheDir);

        $jsonDecoder = new JsonDecoder(new GenericTypePropertyMetadataLoader(
            new DecodeDateTimeTypePropertyMetadataLoader(new DecodeAttributePropertyMetadataLoader(
                new PropertyMetadataLoader($typeResolver),
                $typeResolver,
            )),
            $typeContextFactory,
        ), $decoderCacheDir);

        $this->serializer = new Serializer($jsonEncoder, $jsonDecoder, AutoMapper::create(), $stringTypeResolver);
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
