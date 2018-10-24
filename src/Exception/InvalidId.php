<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Exception;

use Throwable;
use UnexpectedValueException;

class InvalidId extends UnexpectedValueException
{
    private $id;

    public function __construct(string $id, Throwable $previous = null, int $code = 0)
    {
        parent::__construct("'{$id}' is not a valid ID", $code, $previous);

        $this->id = $id;
    }

    public function getId() : string
    {
        return $this->id;
    }
}
