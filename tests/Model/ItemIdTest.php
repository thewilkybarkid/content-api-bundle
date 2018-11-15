<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Model;

use Libero\ContentApiBundle\Exception\InvalidId;
use Libero\ContentApiBundle\Model\ItemId;
use PHPUnit\Framework\TestCase;

final class ItemIdTest extends TestCase
{
    /**
     * @test
     * @dataProvider validProvider
     */
    public function it_accepts_valid_ids(string $value) : void
    {
        $id = ItemId::fromString($value);

        $this->assertSame($value, (string) $id);
    }

    public function validProvider() : iterable
    {
        yield 'minimum' => ['a'];
        yield 'letters' => ['abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'];
        yield 'numbers' => ['01234567890'];
        yield 'percent-encoded' => ['%9F'];
        yield 'other characters' => ['-._~!$&\'()*+,;=:@'];
    }

    /**
     * @test
     * @dataProvider invalidProvider
     */
    public function it_rejects_invalid_ids(string $value) : void
    {
        $this->expectException(InvalidId::class);

        ItemId::fromString($value);
    }

    public function invalidProvider() : iterable
    {
        yield 'other scripts' => ['ほげぴよ'];
        yield 'invalid encoding' => ['foo%2fbar'];
        yield 'lowercase encoding' => ['foo%ZZbar'];
        yield 'slash' => ['foo/bar'];
        yield 'space' => ['foo bar'];
        yield 'other characters' => ['"'];
    }

    /**
     * @test
     * @dataProvider comparisonProvider
     */
    public function it_can_be_compared(string $one, string $two, bool $expected) : void
    {
        $one = ItemId::fromString($one);
        $two = ItemId::fromString($two);

        $this->assertSame($expected, $one->equals($two));
        $this->assertSame($expected, $two->equals($one));
    }

    public function comparisonProvider() : iterable
    {
        yield 'same' => ['foo', 'foo', true];
        yield 'different' => ['bar', 'baz', false];
    }
}
