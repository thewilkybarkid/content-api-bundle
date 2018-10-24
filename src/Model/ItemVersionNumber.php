<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use Libero\ContentApiBundle\Exception\InvalidVersionNumber;

final class ItemVersionNumber
{
    private $version;

    private function __construct(int $version)
    {
        if ($version < 1) {
            throw new InvalidVersionNumber((string) $version);
        }

        $this->version = $version;
    }

    public function __toString() : string
    {
        return (string) $this->version;
    }

    public static function fromInt(int $version) : ItemVersionNumber
    {
        return new ItemVersionNumber($version);
    }

    public static function fromString(string $version) : ItemVersionNumber
    {
        if (((string) (int) $version) !== $version) {
            throw new InvalidVersionNumber($version);
        }

        return new ItemVersionNumber((int) $version);
    }

    public function toInt() : int
    {
        return $this->version;
    }
}
