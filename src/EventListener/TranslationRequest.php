<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

final class TranslationRequest
{
    private $key;
    private $parameters;

    public function __construct(string $key, array $parameters = [])
    {
        $this->key = $key;
        $this->parameters = $parameters;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function getParameters() : array
    {
        return $this->parameters;
    }
}
