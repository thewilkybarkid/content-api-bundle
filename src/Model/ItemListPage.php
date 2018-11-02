<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use ArrayIterator;
use Countable;
use Generator;
use Iterator;
use IteratorIterator;
use function is_array;
use function iterator_count;

final class ItemListPage implements Countable, Iterator
{
    private $cursor;
    private $items;
    private $pointer;

    /**
     * @param iterable<ItemId> $items
     */
    public function __construct(iterable $items, ?string $cursor)
    {
        $this->items = $this->toIterator($items);
        $this->cursor = $cursor;
        $this->pointer = 0;
    }

    public function count() : int
    {
        return iterator_count($this->items);
    }

    public function getCursor() : ?string
    {
        return $this->cursor;
    }

    /**
     * @return ItemId|false
     */
    public function current()
    {
        return $this->items->current();
    }

    public function next() : void
    {
        $this->items->next();
        $this->pointer++;
    }

    public function key() : int
    {
        return $this->pointer;
    }

    public function valid() : bool
    {
        return $this->items->valid();
    }

    public function rewind() : void
    {
        $this->items->rewind();
        $this->pointer = 0;
    }

    private function toIterator(iterable $iterable) : Iterator
    {
        if ($iterable instanceof Generator) {
            $iterator = new ArrayIterator();

            foreach ($iterable as $item) {
                $iterator[] = $item;
            }

            return $iterator;
        }

        if (is_array($iterable)) {
            return new ArrayIterator($iterable);
        }

        return new IteratorIterator($iterable);
    }
}
