<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Model;

use InvalidArgumentException;
use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use UnderflowException;
use function fopen;

final class ItemVersionNumberTest extends TestCase
{
    /**
     * @test
     * @dataProvider validIntegerProvider
     */
    public function it_accepts_valid_integer_version_numbers(int $value) : void
    {
        $versionNumber = ItemVersionNumber::fromInt($value);

        self::assertSame($value, $versionNumber->toInt());
    }

    public function validIntegerProvider() : iterable
    {
        yield 'version 1' => [1];
        yield 'version 2' => [2];
        yield 'version 999' => [999];
    }

    /**
     * @test
     * @dataProvider validStringProvider
     */
    public function it_accepts_valid_string_version_numbers(string $value, int $expected) : void
    {
        $versionNumber = ItemVersionNumber::fromString($value);

        self::assertSame($expected, $versionNumber->toInt());
    }

    public function validStringProvider() : iterable
    {
        foreach ($this->validIntegerProvider() as $key => $arguments) {
            yield $key => [(string) $arguments[0], $arguments[0]];
        }
    }

    /**
     * @test
     * @dataProvider validProvider
     *
     * @param int|string $value
     */
    public function it_accepts_valid_version_numbers($value, int $expected) : void
    {
        $versionNumber = ItemVersionNumber::create($value);

        self::assertSame($expected, $versionNumber->toInt());
    }

    public function validProvider() : iterable
    {
        foreach ($this->validIntegerProvider() as $key => $arguments) {
            yield "integer {$key}" => [$arguments[0], $arguments[0]];
        }
        foreach ($this->validStringProvider() as $key => $arguments) {
            yield "string {$key}" => $arguments;
        }
    }

    /**
     * @test
     * @dataProvider invalidIntegerProvider
     */
    public function it_rejects_invalid_integer_version_numbers(int $value) : void
    {
        $this->expectException(InvalidVersionNumber::class);

        ItemVersionNumber::fromInt($value);
    }

    public function invalidIntegerProvider() : iterable
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
        yield from $this->invalidIntegerProvider();
        yield 'string' => ['foo'];
        yield 'float' => ['1.0'];
        yield 'positive' => ['+1'];
        yield 'octal' => ['0123'];
        yield 'hexadecimal' => ['0x1A'];
        yield 'binary' => ['0b11111111'];
    }

    /**
     * @test
     * @dataProvider invalidProvider
     *
     * @param int|string $value
     */
    public function it_rejects_invalid_version_numbers($value) : void
    {
        $this->expectException(InvalidVersionNumber::class);

        ItemVersionNumber::create($value);
    }

    public function invalidProvider() : iterable
    {
        yield from $this->invalidIntegerProvider();
        yield from $this->invalidStringProvider();
    }

    /**
     * @test
     * @dataProvider invalidTypeProvider
     *
     * @param mixed $value
     */
    public function it_rejects_invalid_version_number_types($value) : void
    {
        $this->expectException(InvalidArgumentException::class);

        ItemVersionNumber::create($value);
    }

    public function invalidTypeProvider() : iterable
    {
        yield 'boolean' => [true];
        yield 'float' => [1.0];
        yield 'null' => [null];
        yield 'object' => [$this];
        yield 'resource' => [fopen('php://memory', 'r')];
    }

    /**
     * @test
     */
    public function it_gets_the_next_number() : void
    {
        $versionNumber1 = ItemVersionNumber::fromInt(1);
        $versionNumber2 = ItemVersionNumber::fromInt(2);
        $versionNumber3 = ItemVersionNumber::fromInt(3);

        self::assertEquals($versionNumber2, $versionNumber1->next());
        self::assertEquals($versionNumber3, $versionNumber2->next());
    }

    /**
     * @test
     */
    public function it_gets_the_previous_number() : void
    {
        $versionNumber1 = ItemVersionNumber::fromInt(1);
        $versionNumber2 = ItemVersionNumber::fromInt(2);
        $versionNumber3 = ItemVersionNumber::fromInt(3);

        self::assertEquals($versionNumber2, $versionNumber3->previous());
        self::assertEquals($versionNumber1, $versionNumber2->previous());
    }

    /**
     * @test
     */
    public function it_cannot_go_below_1() : void
    {
        $this->expectException(UnderflowException::class);

        ItemVersionNumber::fromInt(1)->previous();
    }
}
