<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Exception;

use Throwable;
use UnexpectedValueException;

class InvalidVersionNumber extends UnexpectedValueException
{
    private $version;

    public function __construct(string $version, ?Throwable $previous = null, int $code = 0)
    {
        parent::__construct("'{$version}' is not a valid version number", $code, $previous);

        $this->version = $version;
    }

    public function getVersion() : string
    {
        return $this->version;
    }
}
