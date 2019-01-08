<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Throwable;

final class ItemNotFoundListener
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
        return $exception instanceof ItemNotFound;
    }

    protected function status(CreateApiProblem $event) : int
    {
        return Response::HTTP_NOT_FOUND;
    }

    protected function titleTranslation(CreateApiProblem $event) : TranslationRequest
    {
        return new TranslationRequest('libero.content.item.not_found.title');
    }

    protected function detailsTranslation(CreateApiProblem $event) : ?TranslationRequest
    {
        /** @var ItemNotFound $exception */
        $exception = $event->getException();

        return new TranslationRequest(
            'libero.content.item.not_found.details',
            ['%id%' => $exception->getId()]
        );
    }

    protected function getTranslator() : TranslatorInterface
    {
        return $this->translator;
    }
}
