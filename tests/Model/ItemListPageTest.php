<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Model;

use ArrayIterator;
use Countable;
use EmptyIterator;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use PHPUnit\Framework\TestCase;
use Traversable;
use function call_user_func;
use function count;
use function iterator_to_array;

final class ItemListPageTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_traversable() : void
    {
        $page = new ItemListPage([], null);

        self::assertInstanceOf(Traversable::class, $page);
    }

    /**
     * @test
     */
    public function it_can_be_counted() : void
    {
        $page = new ItemListPage([], null);

        self::assertInstanceOf(Countable::class, $page);
    }

    /**
     * @test
     * @dataProvider iterableProvider
     *
     * @param iterable<int,ItemId> $iterable
     * @param array<int,ItemId> $expected
     */
    public function it_has_item_ids(iterable $iterable, array $expected) : void
    {
        $page = new ItemListPage($iterable, null);

        self::assertEquals($expected, iterator_to_array($page));
        self::assertCount(count($expected), $page);
    }

    /**
     * @test
     * @dataProvider iterableProvider
     *
     * @param iterable<int,ItemId> $iterable
     * @param array<int,ItemId> $expected
     */
    public function it_can_be_traversed(iterable $iterable, array $expected) : void
    {
        $page = new ItemListPage($iterable, null);

        $actual = [];
        foreach ($page as $key => $value) {
            $actual[$key] = $value;
        }

        self::assertEquals($expected, $actual);
    }

    public function iterableProvider() : iterable
    {
        $id1 = ItemId::fromString('item1');
        $id2 = ItemId::fromString('item2');

        $generator1 = call_user_func(
            function () use ($id1, $id2) : Traversable {
                yield $id1;
                yield $id2;
            }
        );

        $generator2 = call_user_func(
            function () use ($id1, $id2) : Traversable {
                yield 'foo' => $id1;
                yield 'foo' => $id2;
            }
        );

        yield 'array' => [[$id1, $id2], [$id1, $id2]];
        yield 'array with keys' => [['foo' => $id1, 'bar' => $id2], [$id1, $id2]];
        yield 'empty iterator' => [new EmptyIterator(), []];
        yield 'array iterator' => [new ArrayIterator([$id1, $id2]), [$id1, $id2]];
        yield 'array iterator with keys' => [new ArrayIterator(['foo' => $id1, 'bar' => $id2]), [$id1, $id2]];
        yield 'generator' => [$generator1, [$id1, $id2]];
        yield 'generator with keys' => [$generator2, [$id1, $id2]];
    }

    /**
     * @test
     */
    public function it_can_be_empty() : void
    {
        $page = new ItemListPage([], null);

        self::assertCount(0, $page);
        self::assertSame([], iterator_to_array($page));
    }

    /**
     * @test
     */
    public function it_may_have_a_cursor() : void
    {
        $with = new ItemListPage([], $cursor = 'foo');
        $withOut = new ItemListPage([], null);

        self::assertSame($cursor, $with->getCursor());
        self::assertNull($withOut->getCursor());
    }
}
