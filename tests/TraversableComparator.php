<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle;

use SebastianBergmann\Comparator\ArrayComparator;
use Traversable;
use function iterator_to_array;

final class TraversableComparator extends ArrayComparator
{
    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    public function accepts($expected, $actual) : bool
    {
        return $expected instanceof Traversable && $actual instanceof Traversable;
    }

    /**
     * @param Traversable $expected
     * @param Traversable $actual
     * @param float $delta
     * @param bool $canonicalize
     * @param bool $ignoreCase
     * @param array<array<mixed>> $processed
     */
    public function assertEquals(
        $expected,
        $actual,
        $delta = 0.0,
        $canonicalize = false,
        $ignoreCase = false,
        array &$processed = []
    ) : void {
        parent::assertEquals(
            iterator_to_array($expected),
            iterator_to_array($actual),
            $delta,
            $canonicalize,
            $ignoreCase,
            $processed
        );
    }
}
