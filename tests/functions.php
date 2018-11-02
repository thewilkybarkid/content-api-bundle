<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle;

use function fopen;
use function fseek;
use function ftell;
use function fwrite;
use function hash_final;
use function hash_init;
use function hash_update_stream;
use function is_int;
use function ob_get_clean;
use function ob_start;
use function rewind;

function capture_output(callable $callback, &$output)
{
    ob_start();

    $return = $callback();

    $output = ob_get_clean();

    return $return;
}

/**
 * @return resource
 */
function stream_from_string(string $string)
{
    /** @var resource $stream */
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $string);
    rewind($stream);

    return $stream;
}

/**
 * @param resource $stream
 */
function hash_for_stream($stream) : string
{
    $context = hash_init('md5');

    keep_stream_position(
        $stream,
        function () use ($stream, $context) : void {
            hash_update_stream($context, $stream);
        }
    );

    return hash_final($context);
}

/**
 * @param resource $stream
 *
 * @return mixed
 */
function keep_stream_position($stream, callable $callable)
{
    $position = ftell($stream);
    rewind($stream);

    $return = $callable();

    if (is_int($position)) {
        fseek($stream, $position);
    }

    return $return;
}
