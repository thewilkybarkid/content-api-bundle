<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Adapter;

use Libero\ContentApiBundle\Adapter\NullItems;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function iterator_to_array;
use function tests\Libero\ContentApiBundle\stream_from_string;

final class NullItemsTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_items() : void
    {
        $items = new NullItems();

        self::assertInstanceOf(Items::class, $items);
    }

    /**
     * @test
     */
    public function it_does_not_add() : void
    {
        $items = new NullItems();

        $item = new ItemVersion(
            ItemId::fromString('foo'),
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to add an item');

        $items->add($item);

        self::assertCount(0, $items);
        self::assertSame([], iterator_to_array($items));
    }

    /**
     * @test
     */
    public function it_does_not_remove() : void
    {
        $items = new NullItems();

        $items->remove(ItemId::fromString('foo'), null);

        self::assertCount(0, $items);
        self::assertSame([], iterator_to_array($items));
    }

    /**
     * @test
     */
    public function it_does_not_remove_versions() : void
    {
        $items = new NullItems();

        $items->remove(ItemId::fromString('foo'), ItemVersionNumber::fromInt(1));

        self::assertCount(0, $items);
        self::assertSame([], iterator_to_array($items));
    }

    /**
     * @test
     */
    public function it_does_not_get() : void
    {
        $items = new NullItems();

        $this->expectException(ItemNotFound::class);

        $items->get(ItemId::fromString('foo'));
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list() : void
    {
        $items = new NullItems();

        self::assertEquals(new ItemListPage([], null), $items->list());
        self::assertEquals(new ItemListPage([], null), $items->list(20));
        self::assertEquals(new ItemListPage([], null), $items->list(10, 'foo'));
    }
}
