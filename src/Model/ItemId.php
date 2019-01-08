<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use Libero\ContentApiBundle\Exception\InvalidId;
use function preg_match;

final class ItemId
{
    /** @var string */
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __toString() : string
    {
        return $this->id;
    }

    public static function fromString(string $id) : ItemId
    {
        $match = preg_match('/^([A-Za-z0-9-._~!$&\'()*+,;=:@]|%[A-F0-9]{2})+$/', $id);

        if (0 === $match) {
            throw new InvalidId($id);
        }

        return new ItemId($id);
    }

    public function equals(ItemId $other) : bool
    {
        return $this->id === $other->id;
    }
}
