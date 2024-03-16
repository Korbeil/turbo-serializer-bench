<?php

namespace Korbeil\TurboSerializerBench;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @BeforeMethods({"initContent", "bootstrap"})
 * @OutputTimeUnit("milliseconds", precision=3)
 */
abstract class AbstractBench implements BenchInterface
{
    protected string $cacheDir;

    protected array $toSerialize;
    protected string $toDeserialize;

    private Filesystem $fs;

    public function __construct()
    {
        $this->cacheDir = sprintf('%s/symfony_test', sys_get_temp_dir());

        $this->fs = new Filesystem();
        $this->fs->remove($this->cacheDir);
        $this->fs->mkdir($this->cacheDir);
    }

    abstract public function bootstrap(): void;

    abstract protected function doBenchSerialize(): string;

    abstract protected function doBenchDeserialize(): array;

    final public function initContent(): void
    {
        $this->toSerialize = [];

        for ($i = 0; $i < 200; $i++) {
            $this->toSerialize[] = new Person();
        }

        $this->toDeserialize = json_encode($this->toSerialize);
    }

    /**
     * @Groups({"serialize"})
     */
    final public function benchSerialize(): void
    {
        $this->doBenchSerialize();
    }

    /**
     * @Groups({"deserialize"})
     */
    final public function benchDeserialize(): void
    {
        $this->doBenchDeserialize();
    }
}
