<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class VersionNotFoundTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_out_of_bounds() : void
    {
        $versionNotFound = new VersionNotFound(ItemId::fromString('foo'), ItemVersionNumber::fromInt(1));

        $this->assertInstanceOf(OutOfBoundsException::class, $versionNotFound);
        $this->assertSame("Item 'foo' does not have a version 1", $versionNotFound->getMessage());
    }

    /**
     * @test
     */
    public function it_has_the_item_id() : void
    {
        $versionNotFound = new VersionNotFound($id = ItemId::fromString('foo'), ItemVersionNumber::fromInt(1));

        $this->assertEquals($id, $versionNotFound->getId());
    }

    /**
     * @test
     */
    public function it_has_the_item_version() : void
    {
        $versionNotFound = new VersionNotFound(ItemId::fromString('foo'), $version = ItemVersionNumber::fromInt(1));

        $this->assertEquals($version, $versionNotFound->getVersion());
    }
}
