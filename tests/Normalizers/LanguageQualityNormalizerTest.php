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
     * @dataProvider provideDifferentQualityValues
     */
    public function it_can_normalize_a_quality($quality, $expected)
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertSame($expected, $normalizer->normalize($quality));
    }

    public function provideDifferentQualityValues(): array
    {
        return [
            'a null quality results in the default weight' => [
                null,
                1,
            ],
            'an empty quality results in the not acceptable' => [
                '',
                0,
            ],
            'a random string results in the not acceptable' => [
                'wrong',
                0,
            ],
            'a numerical string with invalid quality results in the not acceptable' => [
                '2',
                0,
            ],
            'a numerical string with valid quality results in the quality' => [
                '1',
                1,
            ],
            'an invalid negative quality results in the not acceptable' => [
                -1,
                0,
            ],
            'an invalid positive quality results in the provided default' => [
                2,
                0,
            ],
            'a valid quality 0 results in the the not acceptable' => [
                0,
                0,
            ],
            'a valid quality 0.3 results in the quality' => [
                0.3,
                0.3,
            ],
            'a valid quality 1 results in the quality' => [
                1,
                1,
            ],
        ];
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

    public function provideDifferentQualityValuesWithFallback(): array
    {
        return [
            'an empty quality results in the provided fallback' => [
                ['', 0.5],
                0.5,
            ],
            'a null quality results in the provided fallback' => [
                [null, 1.0],
                1,
            ],
            'a random string results in the not acceptable' => [
                ['wrong', 0.5],
                0,
            ],
            'a numerical string with invalid quality results in the not acceptable' => [
                ['2', 0.5],
                0,
            ],
            'a numerical string with valid quality results in the quality' => [
                ['1', 0.5],
                1,
            ],
            'an invalid negative quality results in the not acceptable' => [
                [-1, 0.5],
                0,
            ],
            'an invalid positive quality results in the provided default' => [
                [2, 0.5],
                0,
            ],
            'a valid quality 0 results in the the not acceptable' => [
                [0, 1],
                0,
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
     * @dataProvider provideDifferentAllowEmptyOptions
     */
    public function it_can_normalize_an_empty_quality_with_different_options(array $options, array $range, $expected)
    {
        $normalizer = new LanguageQualityNormalizer($options);

        $this->assertSame($expected, $normalizer->normalizeWithFallback(...$range));
    }

    public function provideDifferentAllowEmptyOptions(): array
    {
        return [
            'an empty quality with the allowed option set to true results in the provided fallback' => [
                ['allow_empty' => true],
                ['', 0.5],
                0.5,
            ],
            'an empty quality with the allowed options set to false results in the not acceptable' => [
                ['allow_empty' => false],
                ['', 0.5],
                0,
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

    public function providedDifferentQualityBoundaryValues(): array
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
