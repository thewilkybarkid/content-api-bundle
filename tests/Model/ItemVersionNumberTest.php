<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Model;

use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;

final class ItemVersionNumberTest extends TestCase
{
    /**
     * @test
     * @dataProvider validProvider
     */
    public function it_accepts_valid_version_numbers(int $value) : void
    {
        $versionNumber = ItemVersionNumber::fromInt($value);

        $this->assertSame($value, $versionNumber->toInt());
    }

    /**
     * @test
     * @dataProvider validProvider
     */
    public function it_accepts_valid_string_version_numbers(int $value) : void
    {
        $versionNumber = ItemVersionNumber::fromString((string) $value);

        $this->assertSame($value, $versionNumber->toInt());
    }

    public function validProvider() : iterable
    {
        yield 'version 1' => [1];
        yield 'version 2' => [2];
        yield 'version 999' => [999];
    }

    /**
     * @test
     * @dataProvider invalidProvider
     */
    public function it_rejects_invalid_version_numbers(int $value) : void
    {
        $this->expectException(InvalidVersionNumber::class);

        ItemVersionNumber::fromInt($value);
    }

    public function invalidProvider() : iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
    }

    /**
     * @test
     * @dataProvider invalidStringProvider
     */
    public function it_rejects_invalid_string_version_numbers(string $value) : void
    {
        $this->expectException(InvalidVersionNumber::class);

        ItemVersionNumber::fromString($value);
    }

    public function invalidStringProvider() : iterable
    {
        yield from $this->invalidProvider();
        yield 'string' => ['foo'];
        yield 'float' => ['1.0'];
        yield 'positive' => ['+1'];
        yield 'octal' => ['0123'];
        yield 'hexadecimal' => ['0x1A'];
        yield 'binary' => ['0b11111111'];
    }

    /**
     * @test
     */
    public function it_gets_the_next_number() : void
    {
        $versionNumber1 = ItemVersionNumber::fromInt(1);
        $versionNumber2 = ItemVersionNumber::fromInt(2);
        $versionNumber3 = ItemVersionNumber::fromInt(3);

        $this->assertEquals($versionNumber2, $versionNumber1->next());
        $this->assertEquals($versionNumber3, $versionNumber2->next());
    }
}
