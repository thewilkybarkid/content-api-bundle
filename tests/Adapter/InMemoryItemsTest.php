<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Adapter;

use Libero\ContentApiBundle\Adapter\InMemoryItems;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Test\ItemsTestCase;

final class InMemoryItemsTest extends ItemsTestCase
{
    protected function createItems() : Items
    {
        return new InMemoryItems();
    }
}
