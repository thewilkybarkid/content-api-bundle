<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Adapter;

use CallbackFilterIterator;
use FilesystemIterator;
use FluentDOM\Utility\Iterators\MapIterator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use LimitIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Traversable;
use function file_exists;
use function fopen;
use function is_dir;
use function iterator_count;
use function iterator_to_array;
use function md5_file;
use function rsort;
use function substr;

final class FilesystemItems implements IteratorAggregate, Items
{
    private $filesystem;
    private $path;

    public function __construct(string $path, Filesystem $filesystem)
    {
        $this->path = '/' === substr($path, -1) ? substr($path, 0, -1) : $path;
        $this->filesystem = $filesystem;
    }

    public function add(ItemVersion $item) : void
    {
        $id = $item->getId();
        $version = $item->getVersion();

        try {
            $current = $this->get($id);
        } catch (ItemNotFound $e) {
            $current = null;
        }

        $next = $current ? $current->getVersion()->next() : ItemVersionNumber::fromInt(1);

        if ($version > $next) {
            throw new UnexpectedVersionNumber($id, $version, $next);
        }

        $this->filesystem->dumpFile("{$this->path}/{$id}/{$version->toInt()}.xml", $item->getContent());
    }

    public function remove(ItemId $id, ?ItemVersionNumber $version) : void
    {
        if (!is_dir("{$this->path}/{$id}")) {
            return;
        }

        if (null === $version) {
            $this->filesystem->remove("{$this->path}/{$id}");

            return;
        }

        try {
            $highest = $this->get($id)->getVersion();
        } catch (ItemNotFound $e) {
            return;
        }

        if ($version->toInt() < $highest->toInt()) {
            throw new UnexpectedVersionNumber($id, $version, $highest);
        }

        if (1 === $version->toInt()) {
            $this->filesystem->remove("{$this->path}/{$id}");

            return;
        }

        $this->filesystem->remove("{$this->path}/{$id}/{$version->toInt()}.xml");
    }

    public function get(ItemId $id, ?ItemVersionNumber $version = null) : ItemVersion
    {
        if (!is_dir("{$this->path}/{$id}")) {
            throw new ItemNotFound($id);
        }

        if (null === $version) {
            $version = $this->getVersions($id)->current();
        }

        if (!$content = @fopen($file = "{$this->path}/{$id}/{$version->toInt()}.xml", 'rb')) {
            if (!file_exists($file)) {
                throw new VersionNotFound($id, $version);
            } else {
                throw new RuntimeException("Unable to open {$file}");
            }
        }

        if (!$hash = md5_file($file)) {
            throw new RuntimeException("Failed to hash {$file}");
        }

        return new ItemVersion($id, $version, $content, $hash);
    }

    public function list(int $limit = 10, ?string $cursor = null) : ItemListPage
    {
        if (null !== $cursor) {
            try {
                $firstId = ItemId::fromString($cursor);
            } catch (InvalidArgumentException $e) {
                return new ItemListPage([], null);
            }
        } else {
            $firstId = null;
        }

        $ids = $this->getIds();

        if (null !== $firstId) {
            $found = null;
            $i = 0;
            foreach ($ids as $id) {
                if ($id->equals($firstId)) {
                    $found = $i;
                    break;
                }
                $i++;
            }

            if (null === $found) {
                return new ItemListPage([], null);
            }
        }

        $ids->rewind();

        $ids = new LimitIterator($ids, $found ?? 0, $limit + 1);
        $ids->seek(($found ?? 0) + $limit);

        $newCursor = $ids->valid() ? (string) $ids->current() : null;

        return new ItemListPage(new LimitIterator($ids, 0, $limit), $newCursor);
    }

    public function count() : int
    {
        return iterator_count($this->getIds());
    }

    public function getIterator() : Traversable
    {
        $items = new MapIterator(
            $this->getIds(),
            function (ItemId $id) : ItemVersion {
                return $this->get($id);
            }
        );

        foreach ($items as $item) {
            yield $item;
        }
    }

    private function getIds() : Iterator
    {
        return new MapIterator(
            new CallbackFilterIterator(
                new FilesystemIterator("{$this->path}/"),
                function (SplFileInfo $file) : bool {
                    return $file->isDir();
                }
            ),
            function (SplFileInfo $file) : ItemId {
                return ItemId::fromString($file->getFilename());
            }
        );
    }

    private function getVersions(ItemId $id) : Iterator
    {
        $versions = new CallbackFilterIterator(
            new MapIterator(
                new FilesystemIterator("{$this->path}/{$id}/"),
                function (SplFileInfo $file) : ?ItemVersionNumber {
                    if (!$file->isFile() || !$file->isReadable() || 'xml' !== $file->getExtension()) {
                        return null;
                    }

                    try {
                        return ItemVersionNumber::fromString($file->getBasename('.xml'));
                    } catch (InvalidArgumentException $e) {
                        return null;
                    }
                }
            ),
            function (?ItemVersionNumber $version) : bool {
                return $version instanceof ItemVersionNumber;
            }
        );

        $versions = iterator_to_array($versions);
        rsort($versions);

        yield from $versions;
    }
}
