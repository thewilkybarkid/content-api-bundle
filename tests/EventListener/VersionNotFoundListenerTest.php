<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Exception;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\EventListener\VersionNotFoundListener;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class VersionNotFoundListenerTest extends TestCase
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
                'libero.content.item.version_not_found.title' => 'es title',
                'libero.content.item.version_not_found.details' => 'es details: %id%, %version%',
            ],
            'es',
            'api_problem'
        );

        $listener = new VersionNotFoundListener($translator);

        $request = new Request();
        $request->setLocale('es');

        $event = new CreateApiProblem(
            $request,
            new VersionNotFound(ItemId::fromString('foo'), ItemVersionNumber::fromInt(1))
        );

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            '<problem xml:lang="es" xmlns="urn:ietf:rfc:7807">
                <status>404</status>
                <title>es title</title>
                <details>es details: foo, 1</details>
            </problem>',
            $event->getDocument()->saveXML()
        );
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions() : void
    {
        $listener = new VersionNotFoundListener(new IdentityTranslator());
        $event = new CreateApiProblem(new Request, new Exception());

        $expected = $event->getDocument()->saveXML();

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            $expected,
            $event->getDocument()->saveXML()
        );
    }
}
