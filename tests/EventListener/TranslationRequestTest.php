<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Libero\ContentApiBundle\EventListener\TranslationRequest;
use PHPUnit\Framework\TestCase;

final class TranslationRequestTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_key() : void
    {
        $translation = new TranslationRequest('key');

        self::assertSame('key', $translation->getKey());
    }

    /**
     * @test
     */
    public function it_may_have_parameters() : void
    {
        $with = new TranslationRequest('key', $parameters = ['foo' => 'bar']);
        $withOut = new TranslationRequest('key');

        self::assertSame($parameters, $with->getParameters());
        self::assertEmpty($withOut->getParameters());
    }
}
