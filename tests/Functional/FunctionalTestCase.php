<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use DirectoryIterator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use tests\Libero\ContentApiBundle\Functional\App\Kernel;
use function tests\Libero\ContentApiBundle\capture_output;

abstract class FunctionalTestCase extends TestCase
{
    /** @var Filesystem */
    private static $filesystem;

    /**
     * @var KernelInterface[]
     */
    private static $kernels = [];

    public static function setUpBeforeClass() : void
    {
        self::$filesystem = new Filesystem();
        parent::setUpBeforeClass();
        foreach (self::getTestCases() as $testCase) {
            self::$kernels[$testCase] = self::createKernel(['test_case' => $testCase]);
        }
    }

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();

        foreach (self::$kernels as $kernel) {
            self::$filesystem->remove($kernel->getCacheDir());
        }

        self::$kernels = [];
    }

    final public function getKernel(string $name) : KernelInterface
    {
        return self::$kernels[$name];
    }

    final protected function captureContent(KernelInterface $kernel, Request $request, ?string &$content) : Response
    {
        return capture_output(
            function () use ($kernel, $request) : Response {
                return $kernel->handle($request);
            },
            $content
        );
    }

    private static function getTestCases() : iterable
    {
        foreach (new DirectoryIterator(__DIR__.'/App/cases') as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }

            yield $fileInfo->getFilename();
        }
    }

    private static function createKernel(array $options = []) : KernelInterface
    {
        if (!isset($options['test_case'])) {
            throw new InvalidArgumentException('The option "test_case" must be set.');
        }
        $kernel = new Kernel(
            $options['test_case'],
            $options['environment'] ?? 'test',
            $options['debug'] ?? true
        );
        $kernel->boot();

        return $kernel;
    }
}
