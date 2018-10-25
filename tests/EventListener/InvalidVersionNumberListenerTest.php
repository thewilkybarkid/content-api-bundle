<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Exception;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\EventListener\InvalidVersionNumberListener;
use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class InvalidVersionNumberListenerTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_translated_properties() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            [
                'libero.content.item.invalid_version.title' => 'es title',
                'libero.content.item.invalid_version.details' => 'es details: %version%',
            ],
            'es',
            'api_problem'
        );

        $listener = new InvalidVersionNumberListener($translator);

        $request = new Request();
        $request->setLocale('es');

        $event = new CreateApiProblem($request, new InvalidVersionNumber('foo'));

        $listener->onCreateApiProblem($event);

        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="es" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>es title</title>
                <details>es details: foo</details>
            </problem>',
            $event->getDocument()->saveXML()
        );
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions() : void
    {
        $listener = new InvalidVersionNumberListener(new IdentityTranslator());
        $event = new CreateApiProblem(new Request, new Exception());

        $expected = $event->getDocument()->saveXML();

        $listener->onCreateApiProblem($event);

        $this->assertXmlStringEqualsXmlString(
            $expected,
            $event->getDocument()->saveXML()
        );
    }
}
