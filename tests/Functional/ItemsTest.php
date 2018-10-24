<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use Libero\ContentApiBundle\Exception\InvalidId;
use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Symfony\Component\HttpFoundation\Request;

final class ItemsTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_has_an_empty_list(string $prefix) : void
    {
        $request = Request::create("/{$prefix}/items");

        $kernel = static::getKernel('Basic');

        $response = $kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertFalse($response->headers->has('Link'), 'Must not have a Link header');
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0" encoding="UTF-8"?><item-list xmlns="http://libero.pub"/>',
            $response->getContent()
        );
    }

    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_returns_an_empty_list_for_a_head_request(string $prefix) : void
    {
        $request = Request::create("/{$prefix}/items", 'HEAD');

        $kernel = static::getKernel('Basic');

        $response = $kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertFalse($response->headers->has('Link'), 'Must not have a Link header');
        $this->assertEmpty($response->getContent());
    }

    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_does_not_find_an_item(string $prefix) : void
    {
        $request = Request::create("/{$prefix}/items/1/versions/1");

        $kernel = static::getKernel('Basic');

        $this->expectException(ItemNotFound::class);

        $kernel->handle($request);
    }

    public function serviceProvider() : iterable
    {
        yield 'service-one' => ['service-one'];
        yield 'service-two' => ['service-two'];
    }

    /**
     * @test
     */
    public function it_recognises_invalid_ids() : void
    {
        $request = Request::create('/service-one/items/foo bar/versions/1');

        $kernel = static::getKernel('Basic');

        $this->expectException(InvalidId::class);

        $kernel->handle($request);
    }

    /**
     * @test
     */
    public function it_recognises_invalid_versions() : void
    {
        $request = Request::create('/service-one/items/foo/versions/foo');

        $kernel = static::getKernel('Basic');

        $this->expectException(InvalidVersionNumber::class);

        $kernel->handle($request);
    }
}
