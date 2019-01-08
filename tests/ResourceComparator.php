<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle;

use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use function fseek;
use function ftell;
use function get_resource_type;
use function hash_final;
use function hash_init;
use function hash_update_stream;
use function is_int;
use function is_resource;
use function preg_match;
use function rewind;
use function stream_get_meta_data;

final class ResourceComparator extends Comparator
{
    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    public function accepts($expected, $actual) : bool
    {
        return is_resource($expected) && is_resource($actual);
    }

    /**
     * @param resource $expected
     * @param resource $actual
     * @param float $delta
     * @param bool $canonicalize
     * @param bool $ignoreCase
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false) : void
    {
        if ($this->asString($actual) !== $this->asString($expected)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->exporter->export($expected),
                $this->exporter->export($actual)
            );
        }
    }

    /**
     * @param resource $resource
     */
    private function asString($resource) : string
    {
        if ('stream' !== get_resource_type($resource)) {
            return (string) $resource;
        }

        $metaData = stream_get_meta_data($resource);
        $match = preg_match('(a\+|c\+|r|w\+|x\+)', $metaData['mode']);

        if (0 === $match) {
            return (string) $resource;
        }

        $position = ftell($resource);
        rewind($resource);

        $context = hash_init('md5');
        hash_update_stream($context, $resource);

        if (is_int($position)) {
            fseek($resource, $position);
        }

        return hash_final($context);
    }
}
