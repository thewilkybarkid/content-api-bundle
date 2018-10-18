<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\DependencyInjection;

use Libero\ContentApiBundle\Routing\Loader;
use Libero\PingController\PingController;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function array_keys;
use function str_replace;

final class ContentApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        foreach (array_keys($config['services']) as $prefix) {
            $config['services'][$prefix]['name'] = str_replace('-', '_', (string) $prefix);
            $this->addContentService($config['services'][$prefix], $container);
        }

        $container->findDefinition(Loader::class)->setArgument(0, $config['services']);
    }

    private function addContentService(array $config, ContainerBuilder $container) : void
    {
        $ping = new Definition(PingController::class);
        $ping->addTag('controller.service_arguments');
        $container->setDefinition("libero.content_api.{$config['name']}.ping", $ping);
    }

    public function getConfiguration(array $config, ContainerBuilder $container) : ConfigurationInterface
    {
        return new ContentApiConfiguration($this->getAlias());
    }

    public function getNamespace() : string
    {
        return 'http://libero.pub/schema/content-api-bundle';
    }

    public function getXsdValidationBasePath() : string
    {
        return __DIR__.'/../Resources/config/schema/content-api-bundle';
    }

    public function getAlias() : string
    {
        return 'content_api';
    }
}
