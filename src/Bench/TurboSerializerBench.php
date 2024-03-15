<?php

namespace Korbeil\TurboSerializerBench\Bench;

use AutoMapper\AutoMapper;
use Korbeil\TurboSerializerBench\AbstractBench;
use Mtarld\JsonEncoderBundle\JsonDecoder;
use Mtarld\JsonEncoderBundle\JsonEncoder;
use Mtarld\JsonEncoderBundle\Mapping\Encode\AttributePropertyMetadataLoader as EncodeAttributePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Encode\DateTimeTypePropertyMetadataLoader as EncodeDateTimeTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Decode\AttributePropertyMetadataLoader as DecodeAttributePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\Decode\DateTimeTypePropertyMetadataLoader as DecodeDateTimeTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\GenericTypePropertyMetadataLoader;
use Mtarld\JsonEncoderBundle\Mapping\PropertyMetadataLoader;
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
        $cacheDir = sprintf('%s/symfony_test', sys_get_temp_dir());
        $encoderCacheDir = $cacheDir . '/json_encoder/encoder';
        $decoderCacheDir = $cacheDir . '/json_encoder/decoder';
        $lazyGhostCacheDir = $cacheDir . '/json_encoder/lazy_ghost';

        if (is_dir($encoderCacheDir)) {
            array_map('unlink', glob($encoderCacheDir.'/*'));
            rmdir($encoderCacheDir);
        }

        if (is_dir($decoderCacheDir)) {
            array_map('unlink', glob($decoderCacheDir.'/*'));
            rmdir($decoderCacheDir);
        }

        if (is_dir($lazyGhostCacheDir)) {
            array_map('unlink', glob($lazyGhostCacheDir.'/*'));
            rmdir($lazyGhostCacheDir);
        }

        $typeContextFactory = new TypeContextFactory($stringTypeResolver = new StringTypeResolver());
        $typeResolver = new TypeResolver(TypeInfoResolver::create(), $typeContextFactory);

        $jsonEncoder = new JsonEncoder(new GenericTypePropertyMetadataLoader(
            new EncodeDateTimeTypePropertyMetadataLoader(new EncodeAttributePropertyMetadataLoader(
                new PropertyMetadataLoader($typeResolver),
                $typeResolver,
            )),
            $typeContextFactory,
        ), $cacheDir);

        $jsonDecoder = new JsonDecoder(new GenericTypePropertyMetadataLoader(
            new DecodeDateTimeTypePropertyMetadataLoader(new DecodeAttributePropertyMetadataLoader(
                new PropertyMetadataLoader($typeResolver),
                $typeResolver,
            )),
            $typeContextFactory,
        ), $cacheDir);

        $this->serializer = new Serializer($jsonEncoder, $jsonDecoder, AutoMapper::create(), $stringTypeResolver);
    }

    protected function doBenchSerialize(array $objects): void
    {
        foreach ($objects as $object) {
            $this->serializer->serialize($object, 'json');
        }
    }

    protected function doBenchDeserialize(string $content, string $type): void
    {
        $this->serializer->deserialize($content, $type, 'json');
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