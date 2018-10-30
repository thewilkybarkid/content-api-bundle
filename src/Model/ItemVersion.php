<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

final class ItemVersion
{
    private $content;
    private $hash;
    private $id;
    private $version;

    /**
     * @param resource $content
     */
    public function __construct(ItemId $id, ItemVersionNumber $version, $content, string $hash)
    {
        $this->id = (string) $id;
        $this->version = $version->toInt();
        $this->content = $content;
        $this->hash = $hash;
    }

    public function getId() : ItemId
    {
        return ItemId::fromString($this->id);
    }

    public function getVersion() : ItemVersionNumber
    {
        return ItemVersionNumber::fromInt($this->version);
    }

    /**
     * @return resource
     */
    public function getContent()
    {
        return $this->content;
    }

    public function getHash() : string
    {
        return $this->hash;
    }
}
