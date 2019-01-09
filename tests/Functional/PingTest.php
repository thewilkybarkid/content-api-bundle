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
        static::bootKernel(['test_case' => 'Basic']);

        $request = Request::create("/{$prefix}/ping");

        $response = self::$kernel->handle($request);

        $this->assertSame('pong', $response->getContent());
    }

    public function serviceProvider() : iterable
    {
        yield 'service-one' => ['service-one'];
        yield 'service-two' => ['service-two'];
    }
}
