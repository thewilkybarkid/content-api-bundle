<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional\App;

use InvalidArgumentException;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use function array_merge;
use function call_user_func_array;
use function file_exists;
use function is_dir;
use function serialize;
use function sys_get_temp_dir;
use function unserialize;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /** @var string */
    private $testCase;

    public function __construct(string $testCase, string $environment, bool $debug)
    {
        if (!is_dir(__DIR__."/cases/{$testCase}")) {
            throw new InvalidArgumentException("The test case '{$testCase}' does not exist.");
        }
        $this->testCase = $testCase;

        parent::__construct("{$environment}_{$testCase}", $debug);
    }

    public function registerBundles() : iterable
    {
        $bundles = require __DIR__.'/bundles.php';

        if (file_exists(__DIR__."/cases/{$this->testCase}/bundles.php")) {
            $bundles = array_merge($bundles, require __DIR__."/cases/{$this->testCase}/bundles.php");
        }

        foreach ($bundles as $bundle) {
            yield new $bundle();
        }
    }

    public function getProjectDir() : string
    {
        return __DIR__;
    }

    public function getCacheDir() : string
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION."/{$this->testCase}/cache/{$this->environment}";
    }

    public function getLogDir() : string
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION."/{$this->testCase}/logs";
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader) : void
    {
        $container->register('logger', NullLogger::class);

        $loader->load(__DIR__.'/config.xml');
        $loader->load(__DIR__."/cases/{$this->testCase}/config.xml");
    }

    protected function configureRoutes(RouteCollectionBuilder $routes) : void
    {
        $routes->import(__DIR__.'/routing.xml');
    }

    public function serialize() : string
    {
        return serialize([$this->testCase, $this->getEnvironment(), $this->isDebug()]);
    }

    /**
     * @param string $str
     */
    public function unserialize($str) : void
    {
        call_user_func_array([$this, '__construct'], unserialize($str));
    }
}
