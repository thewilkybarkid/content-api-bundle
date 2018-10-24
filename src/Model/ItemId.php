<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use Libero\ContentApiBundle\Exception\InvalidId;
use function preg_match;

final class ItemId
{
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
        if (!preg_match('/^([A-Za-z0-9-._~!$&\'()*+,;=:@]|%[A-F0-9]{2})+$/', $id)) {
            throw new InvalidId($id);
        }

        return new ItemId($id);
    }
}
