<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Adapter;

use IteratorAggregate;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Traversable;
use function array_keys;
use function array_map;
use function array_pop;
use function array_search;
use function array_slice;
use function count;
use function max;

final class InMemoryItems implements IteratorAggregate, Items
{
    /** @var array<string,array<int,ItemVersion>> */
    private $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function add(ItemVersion $item) : void
    {
        try {
            $next = $this->get($item->getId())->getVersion()->next();
        } catch (ItemNotFound $e) {
            $next = ItemVersionNumber::fromInt(1);
        }

        if ($item->getVersion() > $next) {
            throw new UnexpectedVersionNumber($item->getId(), $item->getVersion(), $next);
        }

        $this->items[(string) $item->getId()][$item->getVersion()->toInt()] = $item;
    }

    public function remove(ItemId $id, ?ItemVersionNumber $version) : void
    {
        if (null === $version) {
            unset($this->items[(string) $id]);

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
            unset($this->items[(string) $id]);

            return;
        }

        unset($this->items[(string) $id][$version->toInt()]);
    }

    public function get(ItemId $id, ?ItemVersionNumber $version = null) : ItemVersion
    {
        if (!isset($this->items[(string) $id])) {
            throw new ItemNotFound($id);
        }

        if (null === $version) {
            $version = max(array_keys($this->items[(string) $id]));
        } elseif (!isset($this->items[(string) $id][$version->toInt()])) {
            throw new VersionNotFound($id, $version);
        } else {
            $version = $version->toInt();
        }

        return $this->items[(string) $id][$version];
    }

    public function list(int $limit = 10, ?string $cursor = null) : ItemListPage
    {
        $ids = array_keys($this->items);

        if (null !== $cursor) {
            /** @var int|false $offset */
            $offset = array_search($cursor, $ids);

            if (false === $offset) {
                return new ItemListPage([], null);
            }
        }

        /** @var string[] $slice */
        $slice = array_slice($ids, $offset ?? 0, $limit + 1);

        if (count($slice) > $limit) {
            $newCursor = array_pop($slice);
        }

        $ids = array_map([ItemId::class, 'fromString'], $slice);

        return new ItemListPage($ids, $newCursor ?? null);
    }

    public function count() : int
    {
        return count($this->items);
    }

    public function getIterator() : Traversable
    {
        foreach ($this->items as $versions) {
            yield $versions[max(array_keys($versions))];
        }
    }
}
