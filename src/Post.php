<?php

namespace Korbeil\TurboSerializerBench;

class Post
{
    public int $id;
    public string $title;
    public string $summary;

    public function __construct()
    {}

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }
}
