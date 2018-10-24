<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Controller;

use FluentDOM\DOM\Document;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetItemListController
{
    public function __invoke(Request $request) : Response
    {
        $document = new Document();
        $document->registerNamespace('', 'http://libero.pub');

        $document->appendElement('item-list');

        return new Response(
            $document->saveXML(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=utf-8']
        );
    }
}
