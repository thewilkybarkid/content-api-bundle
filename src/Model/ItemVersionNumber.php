<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use InvalidArgumentException;
use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use UnderflowException;
use function gettype;
use function is_int;
use function is_string;

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

    /**
     * @param int|string $version
     */
    public static function create($version) : ItemVersionNumber
    {
        if (is_int($version)) {
            return ItemVersionNumber::fromInt($version);
        }

        if (is_string($version)) {
            return ItemVersionNumber::fromString($version);
        }

        throw new InvalidArgumentException('Expected version number, got '.gettype($version));
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

    public function next() : ItemVersionNumber
    {
        return ItemVersionNumber::fromInt($this->toInt() + 1);
    }

    public function previous() : ItemVersionNumber
    {
        if (1 === $this->version) {
            throw new UnderflowException();
        }

        return ItemVersionNumber::fromInt($this->toInt() - 1);
    }
}
