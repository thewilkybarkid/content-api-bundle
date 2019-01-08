<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use OutOfBoundsException;
use Throwable;

class UnexpectedVersionNumber extends OutOfBoundsException
{
    /** @var ItemVersionNumber */
    private $expected;

    /** @var ItemId */
    private $id;

    /** @var ItemVersionNumber */
    private $version;

    public function __construct(
        ItemId $id,
        ItemVersionNumber $version,
        ItemVersionNumber $expected,
        ?Throwable $previous = null,
        int $code = 0
    ) {
        parent::__construct("Item '{$id}' expected version {$expected}, got {$version}", $code, $previous);

        $this->id = $id;
        $this->version = $version;
        $this->expected = $expected;
    }

    public function getExpected() : ItemVersionNumber
    {
        return $this->expected;
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
