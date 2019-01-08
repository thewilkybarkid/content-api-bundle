<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Test;

use AppendIterator;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use function fopen;
use function fwrite;
use function iterator_to_array;
use function rewind;

abstract class ItemsTestCase extends TestCase
{
    /** @var Items */
    protected $items;

    /**
     * @before
     */
    final protected function setUpItems() : void
    {
        $this->items = $this->createItems();
    }

    /**
     * @test
     */
    final public function it_is_items() : void
    {
        static::assertInstanceOf(Items::class, $this->items);
    }

    /**
     * @test
     */
    final public function it_is_traversable() : void
    {
        for ($i = 1; $i <= 500; $i++) {
            $this->items->add($this->generateItemVersion("item{$i}", 1));
            $this->items->add($this->generateItemVersion("item{$i}", 2));
        }

        static::assertCount(500, $this->items);
        $i = 0;
        foreach ($this->items as $item) {
            $i++;
            static::assertEquals($this->generateItemVersion("item{$i}", 2), $item);
        }
        static::assertSame(500, $i);
    }

    /**
     * @test
     */
    final public function it_adds_and_removes_items() : void
    {
        $this->items->add($item1 = $this->generateItemVersion('foo', 1));
        $this->items->add($item2 = $this->generateItemVersion('bar', 1));

        static::assertCount(2, $this->items);
        static::assertEquals([$item1, $item2], iterator_to_array($this->items));

        $this->items->remove($item1->getId(), null);

        static::assertCount(1, $this->items);
        static::assertEquals([$item2], iterator_to_array($this->items));

        $this->items->remove($item2->getId(), null);

        static::assertCount(0, $this->items);
        static::assertEquals([], iterator_to_array($this->items));
    }

    /**
     * @test
     */
    final public function it_adds_and_removes_versions() : void
    {
        $this->items->add($item1v1 = $this->generateItemVersion('foo', 1));
        $this->items->add($item1v2 = $this->generateItemVersion('foo', 2));

        static::assertCount(1, $this->items);
        static::assertEquals([$item1v2], iterator_to_array($this->items));

        $this->items->remove($item1v2->getId(), $item1v2->getVersion());

        static::assertCount(1, $this->items);
        static::assertEquals([$item1v1], iterator_to_array($this->items));

        $this->items->remove($item1v1->getId(), $item1v1->getVersion());

        static::assertCount(0, $this->items);
        static::assertEquals([], iterator_to_array($this->items));
    }

    /**
     * @test
     */
    final public function it_rejected_unexpected_initial_versions() : void
    {
        $this->expectException(UnexpectedVersionNumber::class);

        $this->items->add($this->generateItemVersion('foo', 2));
    }

    /**
     * @test
     */
    final public function it_rejected_unexpected_versions() : void
    {
        $this->items->add($this->generateItemVersion('foo', 1));

        $this->expectException(UnexpectedVersionNumber::class);

        $this->items->add($this->generateItemVersion('foo', 3));
    }

    /**
     * @test
     */
    final public function it_rejected_removing_early_versions() : void
    {
        $this->items->add($this->generateItemVersion('foo', 1));
        $this->items->add($this->generateItemVersion('foo', 2));

        $this->expectException(UnexpectedVersionNumber::class);

        $this->items->remove(ItemId::fromString('foo'), ItemVersionNumber::fromInt(1));
    }

    /**
     * @test
     */
    final public function it_ignores_removing_items_or_versions_that_do_not_exist() : void
    {
        $this->items->add($item1v1 = $this->generateItemVersion('foo', 1));

        $this->items->remove(ItemId::fromString('foo'), ItemVersionNumber::fromInt(2));
        $this->items->remove(ItemId::fromString('bar'), null);

        static::assertCount(1, $this->items);
        static::assertEquals([$item1v1], iterator_to_array($this->items));
    }

    /**
     * @test
     */
    final public function it_gets_item_versions() : void
    {
        $this->items->add($item1v1 = $this->generateItemVersion('foo', 1));
        $this->items->add($item2v1 = $this->generateItemVersion('foo', 2));
        $this->items->add($item1v2 = $this->generateItemVersion('bar', 1));

        static::assertEquals($item1v1, $this->items->get($item1v1->getId(), $item1v1->getVersion()));
        static::assertEquals($item1v2, $this->items->get($item1v2->getId(), $item1v2->getVersion()));
        static::assertEquals($item2v1, $this->items->get($item2v1->getId(), $item2v1->getVersion()));
    }

    /**
     * @test
     */
    final public function it_may_not_find_items() : void
    {
        $this->expectException(ItemNotFound::class);

        $this->items->get(ItemId::fromString('foo'));
    }

    /**
     * @test
     */
    final public function it_may_not_find_item_versions() : void
    {
        $this->items->add($item = $this->generateItemVersion('foo', 1));

        $this->expectException(VersionNotFound::class);

        $this->items->get($item->getId(), $item->getVersion()->next());
    }

    /**
     * @test
     */
    final public function it_gets_latest_item_versions() : void
    {
        $this->items->add($item1v1 = $this->generateItemVersion('foo', 1));
        $this->items->add($item2v1 = $this->generateItemVersion('foo', 2));
        $this->items->add($item1v2 = $this->generateItemVersion('bar', 1));

        static::assertEquals($item1v2, $this->items->get($item1v2->getId()));
        static::assertEquals($item2v1, $this->items->get($item2v1->getId()));
    }

    /**
     * @test
     */
    final public function it_returns_a_list() : void
    {
        $this->items->add($item1v1 = $this->generateItemVersion('foo', 1));
        $this->items->add($item1v2 = $this->generateItemVersion('foo', 2));
        $this->items->add($item2v1 = $this->generateItemVersion('bar', 1));
        $this->items->add($item3v1 = $this->generateItemVersion('baz', 1));

        $list = $this->items->list();

        static::assertEquals([$item1v2->getId(), $item2v1->getId(), $item3v1->getId()], iterator_to_array($list));
        static::assertNull($list->getCursor());
    }

    /**
     * @test
     */
    final public function it_paginates() : void
    {
        $this->items->add($item1v1 = $this->generateItemVersion('foo', 1));
        $this->items->add($item1v2 = $this->generateItemVersion('foo', 2));
        $this->items->add($item2v1 = $this->generateItemVersion('bar', 1));
        $this->items->add($item3v1 = $this->generateItemVersion('baz', 1));

        $page1 = $this->items->list(1);

        static::assertCount(1, $page1);
        static::assertNotNull($page1->getCursor());

        $page2 = $this->items->list(1, $page1->getCursor());

        static::assertCount(1, $page2);
        static::assertNotNull($page2->getCursor());

        $page3 = $this->items->list(1, $page2->getCursor());

        static::assertCount(1, $page3);
        static::assertNull($page3->getCursor());

        $results = new AppendIterator();
        $results->append($page1);
        $results->append($page2);
        $results->append($page3);

        static::assertEquals(
            [$item1v2->getId(), $item2v1->getId(), $item3v1->getId()],
            iterator_to_array($results, false)
        );
    }

    /**
     * @test
     */
    final public function it_can_return_an_empty_list() : void
    {
        static::assertEquals(new ItemListPage([], null), $this->items->list());
        static::assertEquals(new ItemListPage([], null), $this->items->list(20));
    }

    final protected function generateItemVersion(
        string $id = 'foo',
        int $version = 1,
        string $content = 'foo'
    ) : ItemVersion {
        /** @var resource $stream */
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        return new ItemVersion(
            ItemId::fromString($id),
            ItemVersionNumber::fromInt($version),
            $stream,
            $this->hash($content)
        );
    }

    abstract protected function createItems() : Items;

    protected function hash(string $string) : string
    {
        return $string;
    }
}
