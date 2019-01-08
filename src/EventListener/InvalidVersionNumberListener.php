<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Throwable;

final class InvalidVersionNumberListener
{
    use TranslatingApiProblemListener;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function supports(Throwable $exception) : bool
    {
        return $exception instanceof InvalidVersionNumber;
    }

    protected function status(CreateApiProblem $event) : int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    protected function titleTranslation(CreateApiProblem $event) : TranslationRequest
    {
        return new TranslationRequest('libero.content.item.invalid_version.title');
    }

    protected function detailsTranslation(CreateApiProblem $event) : ?TranslationRequest
    {
        /** @var InvalidVersionNumber $exception */
        $exception = $event->getException();

        return new TranslationRequest(
            'libero.content.item.invalid_version.details',
            ['%version%' => $exception->getVersion()]
        );
    }

    protected function getTranslator() : TranslatorInterface
    {
        return $this->translator;
    }
}
