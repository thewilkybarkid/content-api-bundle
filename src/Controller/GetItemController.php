<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Controller;

use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetItemController
{
    public function __invoke(Request $request, string $id, string $version) : Response
    {
        $id = ItemId::fromString($id);
        $version = ItemVersionNumber::fromString($version);

        throw new ItemNotFound($id);
    }
}
