<?php

namespace Korbeil\TurboSerializerBench;

use PhpBench\Benchmark\Metadata\Annotations\AfterMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @BeforeMethods({"bootstrap", "initObjects", "initContent"})
 * @AfterMethods({"tearDown"})
 * @OutputTimeUnit("milliseconds", precision=3)
 */
abstract class AbstractBench implements BenchInterface
{
    /** @var array<Person> */
    private array $persons = [];

    /** @var array<Post> */
    private array $posts = [];

    private ?string $personContent = null;
    private ?string $postContent = null;

    private string $cacheDir;
    private Filesystem $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
        $this->cacheDir = '/tmp/serializer';
        $this->fs->mkdir($this->cacheDir);
    }

    final public function initObjects(): void
    {
        $persons = [];
        $posts = [];

        for ($i = 0; $i < 200; $i++) {
            $persons[] = (new Person())
                ->setId($i)
                ->setName('Foo ')
                ->setMarried(true)
                ->setFavoriteColors(['blue', 'red'])
                ->setMother(
                    (new Person())
                        ->setId($i+1)
                        ->setName('Foo\'s mother')
                        ->setMarried(false)
                        ->setFavoriteColors(['blue', 'violet'])
                );
        }

        for ($i = 0; $i < 200; $i++) {
            $posts[] = (new Post())
                ->setId($i)
                ->setTitle('Title ' . $i)
                ->setSummary('Summary ' . $i);
        }

        $this->persons = $persons;
        $this->posts = $posts;
    }

    final public function initContent(): void
    {
        $personContent = [];
        for ($i = 0; $i < 200; $i++) {
            $personContent[] = <<<JSON
                {
                    "@type":"Korbeil\AutoMapperBenchmark\Bench\\\Person",
                    "id":${i},
                    "name":"Foo ",
                    "mother":{
                        "@type":"Korbeil\AutoMapperBenchmark\Bench\\\Person",
                        "id":${i},
                        "name":"Foo's mother",
                        "mother":null,
                        "married":false,
                        "favoriteColors":["blue","violet"]
                    },
                    "married":true,
                    "favoriteColors":["blue","red"]
                }
JSON;
        }

        $postContent = [];
        for ($i = 0; $i < 200; $i++) {
            $postContent[] = <<<JSON
                {
                    "@type":"Korbeil\AutoMapperBenchmark\Bench\Post",
                    "id":${i},
                    "title":"Foo",
                    "summary":"Foo"
                }
JSON;
        }

        $this->personContent = sprintf('[%s]', implode(',', $personContent));
        $this->postContent = sprintf('[%s]', implode(',', $postContent));
    }

    /**
     * @Groups({"serialize"})
     */
    final public function benchSerialize(): void
    {
        $this->doBenchSerialize($this->persons);
    }

    /**
     * @Groups({"deserialize"})
     */
    final public function benchDeserialize(): void
    {
        $this->doBenchDeserialize($this->personContent, Person::class);
    }

    /**
     * @Groups({"serialize-reflection"})
     */
    final public function benchSerializeReflection(): void
    {
        $this->doBenchSerialize($this->posts);
    }

    /**
     * @Groups({"deserialize-reflection"})
     */
    final public function benchDeserializeReflection(): void
    {
        $this->doBenchDeserialize($this->postContent, Post::class);
    }

    final protected function getResourceDir(string $suffix = ''): string
    {
        return __DIR__ . '/Resources/' . ltrim($suffix, '/');
    }

    public function tearDown(): void
    {
        $this->fs->remove($this->cacheDir);
    }

    protected function createCacheDir(string ...$dirs): void
    {
        array_walk($dirs, function (string &$dir) {
            $dir = $this->cacheDir . DIRECTORY_SEPARATOR . ltrim($dir, DIRECTORY_SEPARATOR);
        });

        $this->fs->mkdir($dirs);
    }

    protected function getCacheDir(string $suffix = ''): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . ltrim($suffix, DIRECTORY_SEPARATOR);
    }

    abstract public function bootstrap(): void;
    abstract protected function doBenchSerialize(array $objects): void;
    abstract protected function doBenchDeserialize(string $content, string $type): void;
}
