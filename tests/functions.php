<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle;

use function fopen;
use function fwrite;
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
