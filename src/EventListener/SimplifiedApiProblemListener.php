<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Throwable;
use function is_string;

trait SimplifiedApiProblemListener
{
    final public function onCreateApiProblem(CreateApiProblem $event) : void
    {
        /** @var Element $problem */
        $problem = $event->getDocument()->documentElement;
        $exception = $event->getException();

        if (!$this->supports($exception)) {
            return;
        }

        $problem->appendElement('status', (string) $this->status($event));
        $problem->appendElement('title', $this->title($event));

        $details = $this->details($event);

        if (is_string($details)) {
            $problem->appendElement('details', $details);
        }
    }

    abstract protected function supports(Throwable $exception) : bool;

    abstract protected function status(CreateApiProblem $event) : int;

    abstract protected function title(CreateApiProblem $event) : string;

    protected function details(CreateApiProblem $event) : ?string
    {
        return null;
    }
}
