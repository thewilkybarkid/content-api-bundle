<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ContentApiConfiguration implements ConfigurationInterface
{
    private $rootName;

    public function __construct(string $rootName)
    {
        $this->rootName = $rootName;
    }

    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder();

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root($this->rootName);
        $rootNode
            ->fixXmlConfig('service')
            ->children()
                ->append($this->getServicesDefinition())
            ->end()
        ;

        return $treeBuilder;
    }

    private function getServicesDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();

        /** @var ArrayNodeDefinition $servicesNode */
        $servicesNode = $builder->root('services');
        $servicesNode
            ->info('Content APIs to create')
            ->normalizeKeys(false)
            ->useAttributeAsKey('prefix')
            ->arrayPrototype()
                ->children()
                ->end()
            ->end()
        ;

        return $servicesNode;
    }
}
