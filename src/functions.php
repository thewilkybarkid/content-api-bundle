<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle;

use RuntimeException;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;

/**
 * @internal
 *
 * @return mixed
 */
function safely_call(callable $callable)
{
    set_error_handler(
        function (int $code, string $message) : bool {
            throw new RuntimeException($message, $code);
        }
    );

    try {
        return $callable();
    } finally {
        restore_error_handler();
    }
}

/**
 * @internal
 */
function matches(string $pattern, string $subject) : bool
{
    return safely_call(
        function () use ($pattern, $subject) : bool {
            return 0 !== preg_match($pattern, $subject);
        }
    );
}
