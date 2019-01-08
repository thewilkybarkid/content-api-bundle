<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Throwable;

final class VersionNotFoundListener
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
        return $exception instanceof VersionNotFound;
    }

    protected function status(CreateApiProblem $event) : int
    {
        return Response::HTTP_NOT_FOUND;
    }

    protected function titleTranslation(CreateApiProblem $event) : TranslationRequest
    {
        return new TranslationRequest('libero.content.item.version_not_found.title');
    }

    protected function detailsTranslation(CreateApiProblem $event) : ?TranslationRequest
    {
        /** @var VersionNotFound $exception */
        $exception = $event->getException();

        return new TranslationRequest(
            'libero.content.item.version_not_found.details',
            ['%id%' => $exception->getId(), '%version%' => $exception->getVersion()->toInt()]
        );
    }

    protected function getTranslator() : TranslatorInterface
    {
        return $this->translator;
    }
}
