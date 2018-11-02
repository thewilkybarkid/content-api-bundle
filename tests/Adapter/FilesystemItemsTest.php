<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Adapter;

use Libero\ContentApiBundle\Adapter\FilesystemItems;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Libero\ContentApiBundle\Test\ItemsTestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use VirtualFileSystem\FileSystem as VirtualFilesystem;
use function md5;

final class FilesystemItemsTest extends ItemsTestCase
{
    /** @var VirtualFilesystem */
    private $fs;

    /**
     * @test
     */
    public function it_may_fail_to_read_item_versions() : void
    {
        $this->fs->createDirectory('/1');
        $file = $this->fs->createFile('/1/1.xml', 'foo');
        $file->chmod(0000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to open {$file->url()}");

        $this->items->get(ItemId::fromString('1'), ItemVersionNumber::fromInt(1));
    }

    protected function createItems() : Items
    {
        $this->fs = new VirtualFilesystem();

        return new FilesystemItems($this->fs->root()->url(), new Filesystem());
    }

    protected function hash(string $string) : string
    {
        return md5($string);
    }
}
