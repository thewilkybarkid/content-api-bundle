<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use Symfony\Component\HttpFoundation\Request;

final class ItemErrorsTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_does_not_find_an_item() : void
    {
        $request = Request::create('/service/items/1/versions/1');

        $kernel = static::getKernel('ApiProblem');

        $response = $kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
                <status>404</status>
                <title>Item not found</title>
                <details>An item with the ID "1" could not be found.</details>
            </problem>',
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function it_recognises_invalid_ids() : void
    {
        $request = Request::create('/service/items/foo bar/versions/1');

        $kernel = static::getKernel('ApiProblem');

        $response = $kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>Invalid ID</title>
                <details>"foo bar" is not a valid ID.</details>
            </problem>',
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function it_recognises_invalid_versions() : void
    {
        $request = Request::create('/service/items/foo/versions/foo');

        $kernel = static::getKernel('ApiProblem');

        $response = $kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>Invalid version number</title>
                <details>"foo" is not a valid version number.</details>
            </problem>',
            $response->getContent()
        );
    }
}
