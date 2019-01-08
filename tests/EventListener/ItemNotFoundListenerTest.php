<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Exception;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\EventListener\ItemNotFoundListener;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class ItemNotFoundListenerTest extends TestCase
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
                'libero.content.item.not_found.title' => 'es title',
                'libero.content.item.not_found.details' => 'es details: %id%',
            ],
            'es',
            'api_problem'
        );

        $listener = new ItemNotFoundListener($translator);

        $request = new Request();
        $request->setLocale('es');

        $event = new CreateApiProblem($request, new ItemNotFound(ItemId::fromString('foo')));

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            '<problem xml:lang="es" xmlns="urn:ietf:rfc:7807">
                <status>404</status>
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
        $listener = new ItemNotFoundListener(new IdentityTranslator());
        $event = new CreateApiProblem(new Request, new Exception());

        $expected = $event->getDocument()->saveXML();

        $listener->onCreateApiProblem($event);

        self::assertXmlStringEqualsXmlString(
            $expected,
            $event->getDocument()->saveXML()
        );
    }
}
