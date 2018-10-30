<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use Countable;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Traversable;

interface Items extends Countable, Traversable
{
    /**
     * @throws UnexpectedVersionNumber
     */
    public function add(ItemVersion $item) : void;

    /**
     * @throws UnexpectedVersionNumber
     */
    public function remove(ItemId $id, ?ItemVersionNumber $version) : void;

    /**
     * @throws ItemNotFound
     * @throws VersionNotFound
     */
    public function get(ItemId $id, ?ItemVersionNumber $version = null) : ItemVersion;

    public function list(int $limit = 10, ?ItemId $startAt = null) : ItemListPage;
}
