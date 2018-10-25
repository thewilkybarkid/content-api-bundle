<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\DependencyInjection\Compiler;

use Libero\ApiProblemBundle\EventListener\ApiProblemListener;
use LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class ApiProblemPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        if (!$container->hasDefinition(ApiProblemListener::class)) {
            return;
        }

        if (!$container->hasDefinition('translator.default')) {
            throw new LogicException(
                'The Symfony translation component is required. Try running "composer require symfony/translation".'
            );
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('api-problem.xml');
    }
}
