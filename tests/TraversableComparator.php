<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle;

use SebastianBergmann\Comparator\ArrayComparator;
use Traversable;
use function iterator_to_array;

final class TraversableComparator extends ArrayComparator
{
    public function accepts($expected, $actual) : bool
    {
        return $expected instanceof Traversable && $actual instanceof Traversable;
    }

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
