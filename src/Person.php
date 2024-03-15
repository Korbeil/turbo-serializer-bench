<?php

namespace Korbeil\TurboSerializerBench;

use Mtarld\JsonEncoderBundle\Attribute\EncodedName;
use Symfony\Component\Serializer\Attribute\SerializedName;

class Person
{
    #[EncodedName('@id')]
    #[SerializedName('@id')]
    public int $id;

    public string $name;

    public bool $married;

    public array $favoriteColors;
}
