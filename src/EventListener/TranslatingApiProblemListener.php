<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Symfony\Component\Translation\TranslatorInterface;

trait TranslatingApiProblemListener
{
    use SimplifiedApiProblemListener;

    final protected function title(CreateApiProblem $event) : string
    {
        return $this->translate($this->titleTranslation($event), $event);
    }

    final protected function details(CreateApiProblem $event) : ?string
    {
        $translation = $this->detailsTranslation($event);

        if (!$translation instanceof TranslationRequest) {
            return null;
        }

        return $this->translate($translation, $event);
    }

    abstract protected function titleTranslation(CreateApiProblem $event) : TranslationRequest;

    protected function detailsTranslation(CreateApiProblem $event) : ?TranslationRequest
    {
        return null;
    }

    abstract protected function getTranslator() : TranslatorInterface;

    final private function translate(TranslationRequest $translation, CreateApiProblem $event) : string
    {
        /** @var Element $problem */
        $problem = $event->getDocument()->documentElement;

        $locale = $problem->getAttribute('xml:lang') ?? 'en';

        return $this->getTranslator()->trans(
            $translation->getKey(),
            $translation->getParameters(),
            'api_problem',
            $locale
        );
    }
}
