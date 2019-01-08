<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Controller;

use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function feof;
use function flush;
use function fread;
use function rewind;

final class GetItemController
{
    private const CHUNK = 1024;

    /** @var Items */
    private $items;

    public function __construct(Items $items)
    {
        $this->items = $items;
    }

    public function __invoke(Request $request, string $id, string $version) : Response
    {
        $id = ItemId::fromString($id);
        if ('latest' === $version) {
            $version = null;
        } else {
            $version = ItemVersionNumber::fromString($version);
        }

        $item = $this->items->get($id, $version);

        $response = new StreamedResponse(
            function () use ($item) {
                $content = $item->getContent();
                rewind($content);

                while (!feof($content)) {
                    echo fread($content, self::CHUNK);
                    flush();
                }
            },
            Response::HTTP_OK,
            ['ETag' => "\"{$item->getHash()}\""]
        );

        if (!$response->isNotModified($request)) {
            $response->headers->set('Content-Type', 'application/xml; charset=utf-8');
        }

        return $response;
    }
}
