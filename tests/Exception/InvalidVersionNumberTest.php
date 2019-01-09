<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class InvalidVersionNumberTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_unexpected_value() : void
    {
        $invalidVersionNumber = new InvalidVersionNumber('foo');

        $this->assertInstanceOf(UnexpectedValueException::class, $invalidVersionNumber);
        $this->assertSame("'foo' is not a valid version number", $invalidVersionNumber->getMessage());
    }

    /**
     * @test
     */
    public function it_has_the_invalid_version_number() : void
    {
        $invalidVersionNumber = new InvalidVersionNumber('foo');

        $this->assertSame('foo', $invalidVersionNumber->getVersion());
    }
}
