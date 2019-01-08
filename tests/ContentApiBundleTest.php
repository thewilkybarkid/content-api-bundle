<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle;

use Libero\ContentApiBundle\ContentApiBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

final class ContentApiBundleTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_bundle() : void
    {
        $bundle = new ContentApiBundle();

        self::assertInstanceOf(BundleInterface::class, $bundle);
    }
}
