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
    private $nextId;

    /**
     * @param ItemId[] $items
     */
    public function __construct(array $items, ?ItemId $nextId)
    {
        $this->items = $items;
        $this->nextId = $nextId;
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

    public function getNextId() : ?ItemId
    {
        return $this->nextId;
    }
}
