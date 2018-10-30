<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Throwable;

final class UnexpectedVersionNumberListener
{
    use TranslatingApiProblemListener;

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function supports(Throwable $exception) : bool
    {
        return $exception instanceof UnexpectedVersionNumber;
    }

    protected function status(CreateApiProblem $event) : int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    protected function titleTranslation(CreateApiProblem $event) : TranslationRequest
    {
        return new TranslationRequest('libero.content.item.unexpected_version.title');
    }

    protected function detailsTranslation(CreateApiProblem $event) : ?TranslationRequest
    {
        /** @var UnexpectedVersionNumber $exception */
        $exception = $event->getException();

        return new TranslationRequest(
            'libero.content.item.unexpected_version.details',
            [
                '%id%' => $exception->getId(),
                '%version%' => $exception->getVersion()->toInt(),
                '%expected%' => $exception->getExpected()->toInt(),
            ]
        );
    }

    protected function getTranslator() : TranslatorInterface
    {
        return $this->translator;
    }
}
