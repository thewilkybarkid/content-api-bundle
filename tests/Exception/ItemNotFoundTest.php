<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class ItemNotFoundTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_out_of_bounds() : void
    {
        $itemNotFound = new ItemNotFound(ItemId::fromString('foo'));

        self::assertInstanceOf(OutOfBoundsException::class, $itemNotFound);
        self::assertSame("An item with the ID 'foo' could not be found", $itemNotFound->getMessage());
    }

    /**
     * @test
     */
    public function it_has_the_item_id() : void
    {
        $itemNotFound = new ItemNotFound($id = ItemId::fromString('foo'));

        self::assertEquals($id, $itemNotFound->getId());
    }
}
