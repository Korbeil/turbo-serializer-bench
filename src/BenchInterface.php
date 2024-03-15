<?php

namespace Korbeil\TurboSerializerBench;

interface BenchInterface
{
    public function benchSerialize(): void;

    public function benchDeserialize(): void;

    public function getName(): string;

    public function getPackageName(): string;
}
