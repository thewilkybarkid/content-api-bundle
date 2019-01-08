<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Exception;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\EventListener\InvalidIdListener;
use Libero\ContentApiBundle\Exception\InvalidId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class InvalidIdListenerTest extends TestCase
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
                'libero.content.item.invalid_id.title' => 'es title',
                'libero.content.item.invalid_id.details' => 'es details: %id%',
            ],
            'es',
            'api_problem'
        );

        $listener = new InvalidIdListener($translator);

        $request = new Request();
        $request->setLocale('es');

        $event = new CreateApiProblem($request, new InvalidId('foo bar'));

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            '<problem xml:lang="es" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>es title</title>
                <details>es details: foo bar</details>
            </problem>',
            $event->getDocument()->saveXML()
        );
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions() : void
    {
        $listener = new InvalidIdListener(new IdentityTranslator());
        $event = new CreateApiProblem(new Request, new Exception());

        $expected = $event->getDocument()->saveXML();

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            $expected,
            $event->getDocument()->saveXML()
        );
    }
}
