<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use InvalidArgumentException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use tests\Libero\ContentApiBundle\Functional\App\Kernel;
use function tests\Libero\ContentApiBundle\capture_output;

abstract class FunctionalTestCase extends KernelTestCase
{
    /** @var ContainerInterface */
    protected static $container;

    final protected function captureContent(Request $request, &$content) : Response
    {
        return capture_output(
            function () use ($request) : Response {
                return self::$kernel->handle($request);
            },
            $content
        );
    }

    final protected static function bootKernel(array $options = []) : KernelInterface
    {
        $kernel = parent::bootKernel($options);

        if (static::$container instanceof TestContainer) {
            return $kernel;
        }

        // For Symfony < 4.1
        $container = $kernel->getContainer();

        if (!$container instanceof ContainerInterface) {
            throw new LogicException('Could not find the container');
        }

        static::$container = $container;

        return $kernel;
    }

    final protected static function createKernel(array $options = []) : KernelInterface
    {
        if (!isset($options['test_case'])) {
            throw new InvalidArgumentException('The option "test_case" must be set.');
        }

        return new Kernel(
            $options['test_case'],
            $options['environment'] ?? 'test',
            $options['debug'] ?? true
        );
    }

    final protected static function ensureKernelShutdown() : void
    {
        parent::ensureKernelShutdown();

        if (static::$container instanceof ResettableContainerInterface) {
            static::$container->reset();
        }
    }
}
