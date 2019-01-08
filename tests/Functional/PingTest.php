<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use Symfony\Component\HttpFoundation\Request;

final class PingTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function each_service_pings(string $prefix) : void
    {
        $kernel = static::getKernel('Basic');

        $request = Request::create("/{$prefix}/ping");

        $response = $kernel->handle($request);

        self::assertSame('pong', $response->getContent());
    }

    public function serviceProvider() : iterable
    {
        yield 'service-one' => ['service-one'];
        yield 'service-two' => ['service-two'];
    }
}
