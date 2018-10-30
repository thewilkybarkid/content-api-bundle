<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Model;

use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use function stream_get_contents;
use function tests\Libero\ContentApiBundle\stream_from_string;

final class ItemVersionTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_an_id() : void
    {
        $item = new ItemVersion(
            $id = ItemId::fromString('foo'),
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $this->assertEquals($id, $item->getId());
    }

    /**
     * @test
     */
    public function it_has_a_version_number() : void
    {
        $item = new ItemVersion(
            ItemId::fromString('foo'),
            $version = ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            'foo'
        );

        $this->assertEquals($version, $item->getVersion());
    }

    /**
     * @test
     */
    public function it_has_content() : void
    {
        $item = new ItemVersion(
            ItemId::fromString('foo'),
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo bar'),
            'foo'
        );

        $this->assertSame('foo bar', stream_get_contents($item->getContent(), -1, 0));
    }

    /**
     * @test
     */
    public function it_has_a_hash() : void
    {
        $item = new ItemVersion(
            ItemId::fromString('foo'),
            ItemVersionNumber::fromInt(1),
            stream_from_string('foo'),
            $hash = 'foobarbaz'
        );

        $this->assertSame($hash, $item->getHash());
    }
}
