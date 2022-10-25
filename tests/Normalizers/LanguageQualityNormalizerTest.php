<?php

namespace Kudashevs\AcceptLanguage\Tests\Normalizers;

use Kudashevs\AcceptLanguage\Normalizers\LanguageQualityNormalizer;
use PHPUnit\Framework\TestCase;

class LanguageQualityNormalizerTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertNotEmpty($normalizer->normalize(1, 1.0));
    }
}
