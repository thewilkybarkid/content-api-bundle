<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Adapter;

use EmptyIterator;
use IteratorAggregate;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use RuntimeException;
use Traversable;

final class NullItems implements IteratorAggregate, Items
{
    public function add(ItemVersion $item) : void
    {
        throw new RuntimeException('Unable to add an item');
    }

    public function remove(ItemId $id, ?ItemVersionNumber $version) : void
    {
        return;
    }

    public function get(ItemId $id, ?ItemVersionNumber $version = null) : ItemVersion
    {
        throw new ItemNotFound($id);
    }

    public function list(int $limit = 10, ?ItemId $startAt = null) : ItemListPage
    {
        return new ItemListPage([], null);
    }

    public function count() : int
    {
        return 0;
    }

    public function getIterator() : Traversable
    {
        return new EmptyIterator();
    }
}
