<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Model;

use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

final class ItemListPageTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_item_ids() : void
    {
        $id1 = ItemId::fromString('item1');
        $id2 = ItemId::fromString('item2');

        $page = new ItemListPage([$id1, $id2], null);

        $this->assertCount(2, $page);
        $this->assertEquals([$id1, $id2], iterator_to_array($page));
    }

    /**
     * @test
     */
    public function it_can_be_empty() : void
    {
        $page = new ItemListPage([], null);

        $this->assertCount(0, $page);
        $this->assertSame([], iterator_to_array($page));
    }

    /**
     * @test
     */
    public function it_may_have_a_next_id() : void
    {
        $with = new ItemListPage([], $id = ItemId::fromString('foo'));
        $withOut = new ItemListPage([], null);

        $this->assertSame($id, $with->getNextId());
        $this->assertNull($withOut->getNextId());
    }
}
