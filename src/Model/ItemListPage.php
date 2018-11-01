<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use Countable;
use IteratorAggregate;
use Traversable;
use function count;

final class ItemListPage implements Countable, IteratorAggregate
{
    private $items;
    private $cursor;

    /**
     * @param ItemId[] $items
     */
    public function __construct(array $items, ?string $cursor)
    {
        $this->items = $items;
        $this->cursor = $cursor;
    }

    public function count() : int
    {
        return count($this->items);
    }

    /**
     * @return Traversable|ItemId[]
     */
    public function getIterator() : Traversable
    {
        yield from $this->items;
    }

    public function getCursor() : ?string
    {
        return $this->cursor;
    }
}
