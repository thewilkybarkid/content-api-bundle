<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Routing;

use Symfony\Component\Config\Loader\Loader as BaseLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class Loader extends BaseLoader
{
    /** @var array<string,array<string,string>> */
    private $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * @param mixed $resource
     * @param ?string $type
     */
    public function load($resource, $type = null) : RouteCollection
    {
        $routes = new RouteCollectionBuilder();

        foreach ($this->services as $prefix => $config) {
            $service = new RouteCollectionBuilder();

            $service->add('ping', "libero.content_api.{$config['name']}.ping");

            $service->add('items', "libero.content_api.{$config['name']}.item_list.get")
                ->setRequirement('_format', 'xml')
                ->setMethods('GET');

            $service->add('items/{id}/versions/{version}', "libero.content_api.{$config['name']}.item.get")
                ->setRequirement('_format', 'xml')
                ->setMethods('GET');

            $routes->mount($prefix, $service);
        }

        return $routes->build();
    }

    /**
     * @param mixed $resource
     * @param ?string $type
     */
    public function supports($resource, $type = null) : bool
    {
        return 'content_api' === $type;
    }
}
