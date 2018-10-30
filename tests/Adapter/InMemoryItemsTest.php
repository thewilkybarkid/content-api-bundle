<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Adapter;

use Libero\ContentApiBundle\Adapter\InMemoryItems;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;
use function tests\Libero\ContentApiBundle\stream_from_string;

final class InMemoryItemsTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_items() : void
    {
        $items = new InMemoryItems();

        $this->assertInstanceOf(Items::class, $items);
    }

    /**
     * @test
     */
    public function it_adds_and_removes_items() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');
        $item2Id = ItemId::fromString('bar');

        $item1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $item2 = new ItemVersion(
            $item2Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('bar'),
            'bar'
        );

        $items->add($item1);
        $items->add($item2);

        $this->assertCount(2, $items);
        $this->assertEquals([$item1, $item2], iterator_to_array($items));

        $items->remove($item1Id, null);

        $this->assertCount(1, $items);
        $this->assertEquals([$item2], iterator_to_array($items));

        $items->remove($item2Id, null);

        $this->assertCount(0, $items);
        $this->assertEquals([], iterator_to_array($items));
    }

    /**
     * @test
     */
    public function it_adds_and_removes_versions() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            stream_from_string('foo'),
            'foo'
        );

        $items->add($item1v1);
        $items->add($item1v2);

        $this->assertCount(1, $items);
        $this->assertEquals([$item1v2], iterator_to_array($items));

        $items->remove($item1Id, ItemVersionNumber::fromInt(2));

        $this->assertCount(1, $items);
        $this->assertEquals([$item1v1], iterator_to_array($items));

        $items->remove($item1Id, ItemVersionNumber::fromInt(1));

        $this->assertCount(0, $items);
        $this->assertEquals([], iterator_to_array($items));
    }

    /**
     * @test
     */
    public function it_rejected_unexpected_initial_versions() : void
    {
        $items = new InMemoryItems();

        $item = new ItemVersion(
            ItemId::fromString('foo'),
            ItemVersionNumber::fromInt(2),
            stream_from_string('foo'),
            'foo'
        );

        $this->expectException(UnexpectedVersionNumber::class);

        $items->add($item);
    }

    /**
     * @test
     */
    public function it_rejected_unexpected_versions() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $item1v3 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(3),
            stream_from_string('foo'),
            'foo'
        );

        $items->add($item1v1);

        $this->expectException(UnexpectedVersionNumber::class);

        $items->add($item1v3);
    }

    /**
     * @test
     */
    public function it_rejected_removing_early_versions() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            stream_from_string('foo'),
            'foo'
        );

        $items->add($item1v1);
        $items->add($item1v2);

        $this->expectException(UnexpectedVersionNumber::class);

        $items->remove($item1Id, ItemVersionNumber::fromInt(1));
    }

    /**
     * @test
     */
    public function it_ignores_removing_items_or_versions_that_do_not_exist() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $items->add($item1v1);

        $items->remove(ItemId::fromString('foo'), ItemVersionNumber::fromInt(2));
        $items->remove(ItemId::fromString('bar'), null);

        $this->assertCount(1, $items);
        $this->assertEquals([$item1v1], iterator_to_array($items));
    }

    /**
     * @test
     */
    public function it_gets_item_versions() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');
        $item2Id = ItemId::fromString('bar');

        $v1 = ItemVersionNumber::fromInt(1);
        $v2 = ItemVersionNumber::fromInt(2);

        $item1v1 = new ItemVersion(
            $item1Id,
            $v1,
            stream_from_string('foo'),
            'foo'
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            $v2,
            stream_from_string('foo'),
            'foo'
        );

        $item2v1 = new ItemVersion(
            $item2Id,
            $v1,
            stream_from_string('bar'),
            'bar'
        );

        $items->add($item1v1);
        $items->add($item2v1);
        $items->add($item1v2);

        $this->assertEquals($item1v1, $items->get($item1Id, $v1));
        $this->assertEquals($item1v2, $items->get($item1Id, $v2));
        $this->assertEquals($item2v1, $items->get($item2Id, $v1));
    }

    /**
     * @test
     */
    public function it_may_not_find_items() : void
    {
        $items = new InMemoryItems();

        $this->expectException(ItemNotFound::class);

        $items->get(ItemId::fromString('foo'));
    }

    /**
     * @test
     */
    public function it_may_not_find_item_versions() : void
    {
        $items = new InMemoryItems();

        $itemId = ItemId::fromString('foo');

        $item1v1 = new ItemVersion(
            $itemId,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $items->add($item1v1);

        $this->expectException(VersionNotFound::class);

        $items->get($itemId, ItemVersionNumber::fromInt(2));
    }

    /**
     * @test
     */
    public function it_gets_latest_item_versions() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');
        $item2Id = ItemId::fromString('bar');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            stream_from_string('foo'),
            'foo'
        );

        $item2v1 = new ItemVersion(
            $item2Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('bar'),
            'bar'
        );

        $items->add($item1v1);
        $items->add($item2v1);
        $items->add($item1v2);

        $this->assertEquals($item1v2, $items->get($item1Id));
        $this->assertEquals($item2v1, $items->get($item2Id));
    }

    /**
     * @test
     */
    public function it_returns_a_list() : void
    {
        $items = new InMemoryItems();

        $item1Id = ItemId::fromString('foo');
        $item2Id = ItemId::fromString('bar');
        $item3Id = ItemId::fromString('baz');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            stream_from_string('foo'),
            'foo'
        );

        $item2v1 = new ItemVersion(
            $item2Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('bar'),
            'bar'
        );

        $item3v1 = new ItemVersion(
            $item3Id,
            ItemVersionNumber::fromInt(1),
            stream_from_string('bar'),
            'bar'
        );

        $items->add($item1v1);
        $items->add($item2v1);
        $items->add($item1v2);
        $items->add($item3v1);

        $this->assertEquals(new ItemListPage([$item1Id, $item2Id, $item3Id], null), $items->list());
        $this->assertEquals(new ItemListPage([$item1Id, $item2Id], $item3Id), $items->list(2));
        $this->assertEquals(new ItemListPage([$item3Id], null), $items->list(2, $item3Id));
    }

    /**
     * @test
     */
    public function it_can_return_an_empty_list() : void
    {
        $items = new InMemoryItems();

        $this->assertEquals(new ItemListPage([], null), $items->list());
        $this->assertEquals(new ItemListPage([], null), $items->list(20));
        $this->assertEquals(new ItemListPage([], null), $items->list(10, ItemId::fromString('foo')));
    }
}
