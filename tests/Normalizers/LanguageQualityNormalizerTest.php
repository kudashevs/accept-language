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

        $this->assertNotEmpty($normalizer->normalize(1));
    }

    /**
     * @test
     * @dataProvider provideDifferentQualityValuesWithFallback
     */
    public function it_can_normalize_a_quality_with_fallback(array $range, $expected)
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertSame($expected, $normalizer->normalizeWithFallback(...$range));
    }

    public function provideDifferentQualityValuesWithFallback()
    {
        return [
            'a random string results in the not acceptable' => [
                ['wrong', 0.5],
                0,
            ],
            'an invalid negative quality results in the not acceptable' => [
                [-1, 0.5],
                0,
            ],
            'an invalid positive quality results in the provided default' => [
                [2, 0.5],
                0,
            ],
            'a null quality results in the provided default' => [
                [null, 1.0],
                1,
            ],
            'an empty quality results in the provided default' => [
                ['', 0.5],
                0.5,
            ],
            'a valid quality 0.3 results in the quality' => [
                [0.3, 0.5],
                0.3,
            ],
            'a valid quality 1 results in the quality' => [
                [1, 0.5],
                1,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providedDifferentQualityBoundaryValues
     */
    public function it_can_normalize_at_boundaries($quality, $expected)
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertSame($expected, $normalizer->normalize($quality));
    }

    public function providedDifferentQualityBoundaryValues()
    {
        return [
            'a negative out of bounds 0.001 results in the not acceptable' => [
                -0.001,
                0,
            ],
            'a zero value results in the not acceptable' => [
                0,
                0,
            ],
            'a positive within bounds 0.001 results in the quality' => [
                0.001,
                0.001,
            ],
            'a positive within bounds 0.999 results in the quality' => [
                0.999,
                0.999,
            ],
            'a positive withing bounds 1 results in the quality' => [
                1,
                1,
            ],
            'a positive out of bounds 1.001 results in the not acceptable' => [
                1.001,
                0,
            ],
        ];
    }
}
