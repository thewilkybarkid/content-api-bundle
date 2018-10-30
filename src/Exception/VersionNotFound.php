<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use OutOfBoundsException;
use Throwable;

class VersionNotFound extends OutOfBoundsException
{
    private $id;
    private $version;

    public function __construct(ItemId $id, ItemVersionNumber $version, Throwable $previous = null, int $code = 0)
    {
        parent::__construct("Item '{$id}' does not have a version {$version}", $code, $previous);

        $this->id = $id;
        $this->version = $version;
    }

    public function getId() : ItemId
    {
        return $this->id;
    }

    public function getVersion() : ItemVersionNumber
    {
        return $this->version;
    }
}
