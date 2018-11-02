<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Adapter;

use Libero\ContentApiBundle\Adapter\FilesystemItems;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use VirtualFileSystem\FileSystem as VirtualFilesystem;
use function iterator_to_array;
use function tests\Libero\ContentApiBundle\hash_for_stream;
use function tests\Libero\ContentApiBundle\stream_from_string;

final class FilesystemItemsTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_items() : void
    {
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $this->assertInstanceOf(Items::class, $items);
    }

    /**
     * @test
     */
    public function it_adds_and_removes_items() : void
    {
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('1');
        $item2Id = ItemId::fromString('2');

        $item1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $item2 = new ItemVersion(
            $item2Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('2 v1'),
            hash_for_stream($stream)
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
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('1');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            $stream = stream_from_string('1 v2'),
            hash_for_stream($stream)
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
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item = new ItemVersion(
            ItemId::fromString('1'),
            ItemVersionNumber::fromInt(2),
            $stream = stream_from_string('1 v2'),
            hash_for_stream($stream)
        );

        $this->expectException(UnexpectedVersionNumber::class);

        $items->add($item);
    }

    /**
     * @test
     */
    public function it_rejected_unexpected_versions() : void
    {
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('1');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $item1v3 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(3),
            $stream = stream_from_string('1 v3'),
            hash_for_stream($stream)
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
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('1');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            $stream = stream_from_string('1 v2'),
            hash_for_stream($stream)
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
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('1');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $items->add($item1v1);

        $items->remove(ItemId::fromString('1'), ItemVersionNumber::fromInt(2));
        $items->remove(ItemId::fromString('2'), null);

        $this->assertCount(1, $items);
        $this->assertEquals([$item1v1], iterator_to_array($items));
    }

    /**
     * @test
     */
    public function it_gets_item_versions() : void
    {
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('.hidden');
        $item2Id = ItemId::fromString('2');

        $v1 = ItemVersionNumber::fromInt(1);
        $v2 = ItemVersionNumber::fromInt(2);

        $item1v1 = new ItemVersion(
            $item1Id,
            $v1,
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            $v2,
            $stream = stream_from_string('1 v2'),
            hash_for_stream($stream)
        );

        $item2v1 = new ItemVersion(
            $item2Id,
            $v1,
            $stream = stream_from_string('2 v1'),
            hash_for_stream($stream)
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
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $this->expectException(ItemNotFound::class);

        $items->get(ItemId::fromString('1'));
    }

    /**
     * @test
     */
    public function it_may_fail_to_read_item_versions() : void
    {
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $fs->createDirectory('/1');
        $file = $fs->createFile('/1/1.xml', 'foo');
        $file->chmod(0000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to open {$file->url()}");

        $items->get(ItemId::fromString('1'), ItemVersionNumber::fromInt(1));
    }

    /**
     * @test
     */
    public function it_may_not_find_item_versions() : void
    {
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $itemId = ItemId::fromString('1');

        $item1v1 = new ItemVersion(
            $itemId,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
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
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('1');
        $item2Id = ItemId::fromString('2');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            $stream = stream_from_string('1 v2'),
            hash_for_stream($stream)
        );

        $item2v1 = new ItemVersion(
            $item2Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('2 v1'),
            hash_for_stream($stream)
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
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $item1Id = ItemId::fromString('.hidden');
        $item2Id = ItemId::fromString('2');
        $item3Id = ItemId::fromString('3');

        $item1v1 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('1 v1'),
            hash_for_stream($stream)
        );

        $item1v2 = new ItemVersion(
            $item1Id,
            ItemVersionNumber::fromInt(2),
            $stream = stream_from_string('1 v2'),
            hash_for_stream($stream)
        );

        $item2v1 = new ItemVersion(
            $item2Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('2 v1'),
            hash_for_stream($stream)
        );

        $item3v1 = new ItemVersion(
            $item3Id,
            ItemVersionNumber::fromInt(1),
            $stream = stream_from_string('3 v1'),
            hash_for_stream($stream)
        );

        $items->add($item1v1);
        $items->add($item2v1);
        $items->add($item1v2);
        $items->add($item3v1);

        $this->assertEquals(new ItemListPage([$item1Id, $item2Id, $item3Id], null), $items->list());
        $this->assertEquals(new ItemListPage([$item1Id, $item2Id], (string) $item3Id), $items->list(2));
        $this->assertEquals(new ItemListPage([$item3Id], null), $items->list(2, (string) $item3Id));
    }

    /**
     * @test
     */
    public function it_can_return_an_empty_list() : void
    {
        $fs = new VirtualFilesystem();
        $items = new FilesystemItems($fs->root()->url(), new Filesystem());

        $this->assertEquals(new ItemListPage([], null), $items->list());
        $this->assertEquals(new ItemListPage([], null), $items->list(20));
        $this->assertEquals(new ItemListPage([], null), $items->list(10, 'foo'));
    }
}
