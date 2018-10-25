<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle;

use Libero\ContentApiBundle\DependencyInjection\Compiler\ApiProblemPass;
use Libero\ContentApiBundle\DependencyInjection\ContentApiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ContentApiBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        $container->addCompilerPass(new ApiProblemPass());
    }

    protected function createContainerExtension() : ExtensionInterface
    {
        return new ContentApiExtension();
    }
}
