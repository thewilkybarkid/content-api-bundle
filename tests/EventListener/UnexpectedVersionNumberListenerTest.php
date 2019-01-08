<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Exception;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\EventListener\UnexpectedVersionNumberListener;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class UnexpectedVersionNumberListenerTest extends TestCase
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
                'libero.content.item.unexpected_version.title' => 'es title',
                'libero.content.item.unexpected_version.details' => 'es details: %id%, %version%, %expected%',
            ],
            'es',
            'api_problem'
        );

        $listener = new UnexpectedVersionNumberListener($translator);

        $request = new Request();
        $request->setLocale('es');

        $event = new CreateApiProblem(
            $request,
            new UnexpectedVersionNumber(
                ItemId::fromString('foo'),
                ItemVersionNumber::fromInt(2),
                ItemVersionNumber::fromInt(1)
            )
        );

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            '<problem xml:lang="es" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>es title</title>
                <details>es details: foo, 2, 1</details>
            </problem>',
            $event->getDocument()->saveXML()
        );
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions() : void
    {
        $listener = new UnexpectedVersionNumberListener(new IdentityTranslator());
        $event = new CreateApiProblem(new Request, new Exception());

        $expected = $event->getDocument()->saveXML();

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            $expected,
            $event->getDocument()->saveXML()
        );
    }
}
