<?php

namespace Korbeil\TurboSerializerBench;

class Person
{
    public ?int $id = null;
    public ?string $name = null;
    public ?Person $mother = null;
    public ?bool $married = null;
    public array $favoriteColors = [];

    public function __construct()
    {}

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Person
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Person
    {
        $this->name = $name;
        return $this;
    }

    public function getMother(): ?Person
    {
        return $this->mother;
    }

    public function setMother(?Person $mother): Person
    {
        $this->mother = $mother;
        return $this;
    }

    public function isMarried(): bool
    {
        return $this->married;
    }

    public function getMarried(): bool
    {
        return $this->married;
    }

    public function setMarried(bool $married): Person
    {
        $this->married = $married;
        return $this;
    }

    public function getFavoriteColors(): array
    {
        return $this->favoriteColors;
    }

    public function setFavoriteColors(array $favoriteColors): Person
    {
        $this->favoriteColors = $favoriteColors;
        return $this;
    }
}
