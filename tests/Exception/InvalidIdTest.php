<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Exception\InvalidId;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class InvalidIdTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_unexpected_value() : void
    {
        $invalidId = new InvalidId('foo bar');

        self::assertInstanceOf(UnexpectedValueException::class, $invalidId);
        self::assertSame("'foo bar' is not a valid ID", $invalidId->getMessage());
    }

    /**
     * @test
     */
    public function it_has_the_invalid_id() : void
    {
        $invalidId = new InvalidId('foo bar');

        self::assertSame('foo bar', $invalidId->getId());
    }
}
