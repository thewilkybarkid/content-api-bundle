<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Controller;

use FluentDOM\DOM\Document;
use Libero\ContentApiBundle\Model\Items;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function http_build_query;
use const PHP_QUERY_RFC3986;

final class GetItemListController
{
    private const CURSOR = 'cursor';
    private const PER_PAGE = 'per-page';
    private const DEFAULT_PER_PAGE = 20;

    private $items;
    private $servicePrefix;

    public function __construct(Items $items, string $servicePrefix)
    {
        $this->items = $items;
        $this->servicePrefix = $servicePrefix;
    }

    public function __invoke(Request $request) : Response
    {
        $ids = $this->items->list(
            $request->query->getInt(self::PER_PAGE, self::DEFAULT_PER_PAGE),
            $request->query->get(self::CURSOR)
        );

        $document = new Document();
        $document->registerNamespace('', 'http://libero.pub');

        $list = $document->appendElement('item-list');

        foreach ($ids as $id) {
            $list->appendElement('item-ref', '', ['id' => $id, 'service' => $this->servicePrefix]);
        }

        $response = new Response(
            $document->saveXML(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=utf-8']
        );

        if ($ids->getCursor()) {
            $query = clone $request->query;
            $query->set(self::CURSOR, $ids->getCursor());
            if (self::DEFAULT_PER_PAGE === $query->getInt(self::PER_PAGE, self::DEFAULT_PER_PAGE)) {
                $query->remove(self::PER_PAGE);
            }

            $next = $request->getBaseUrl().
                $request->getPathInfo().
                '?'.http_build_query($query->all(), '', '&', PHP_QUERY_RFC3986);

            $response->headers->set(
                'Link',
                "<{$next}>; rel=\"next\""
            );
        }

        return $response;
    }
}
