<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Model\ItemId;
use OutOfBoundsException;
use Throwable;

class ItemNotFound extends OutOfBoundsException
{
    /** @var ItemId */
    private $id;

    public function __construct(ItemId $id, ?Throwable $previous = null, int $code = 0)
    {
        parent::__construct("An item with the ID '{$id}' could not be found", $code, $previous);

        $this->id = $id;
    }

    public function getId() : ItemId
    {
        return $this->id;
    }
}
