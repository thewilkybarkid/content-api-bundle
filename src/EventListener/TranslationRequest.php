<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

final class TranslationRequest
{
    /** @var string */
    private $key;
    /** @var array<string,mixed> */
    private $parameters;

    /**
     * @param array<string,mixed> $parameters
     */
    public function __construct(string $key, array $parameters = [])
    {
        $this->key = $key;
        $this->parameters = $parameters;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @return array<string,mixed>
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}
