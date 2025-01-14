<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Normalizers;

use Kudashevs\AcceptLanguage\Normalizers\LanguageQualityNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LanguageQualityNormalizerTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated(): void
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertNotEmpty($normalizer->normalize(1));
    }

    #[Test]
    #[DataProvider('provideDifferentQualityValues')]
    public function it_can_normalize_a_quality($quality, $expected): void
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertSame($expected, $normalizer->normalize($quality));
    }

    public static function provideDifferentQualityValues(): array
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

    #[Test]
    #[DataProvider('provideDifferentQualityValuesWithFallback')]
    public function it_can_normalize_a_quality_with_fallback($quality, array $options, $expected): void
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertSame($expected, $normalizer->normalize($quality, $options));
    }

    public static function provideDifferentQualityValuesWithFallback(): array
    {
        return [
            'a null quality with an invalid fallback results in the not acceptable' => [
                null,
                ['fallback' => -1],
                1,
            ],
            'a null quality with a valid fallback 0.5 results in the provided fallback' => [
                null,
                ['fallback' => 0.5],
                0.5,
            ],
            'a null quality with a valid fallback 1.0 results in the provided fallback' => [
                null,
                ['fallback' => 1.0],
                1,
            ],
            'an empty quality with an invalid fallback results in the not acceptable' => [
                '',
                ['fallback' => 2],
                0,
            ],
            'an empty quality with a valid fallback 0.5 results in the provided fallback' => [
                '',
                ['fallback' => 0.5],
                0.5,
            ],
            'an empty quality with a valid fallback 1.0 results in the provided fallback' => [
                '',
                ['fallback' => 1.0],
                1,
            ],
            'a random string with a valid fallback results in the not acceptable' => [
                'wrong',
                ['fallback' => 0.5],
                0,
            ],
            'a numerical string with an invalid quality and a valid fallback results in the not acceptable' => [
                '2',
                ['fallback' => 0.5],
                0,
            ],
            'a numerical string with a valid quality and a valid fallback results in the quality' => [
                '1',
                ['fallback' => 0.5],
                1,
            ],
            'an invalid negative quality with a valid fallback results in the not acceptable' => [
                -1,
                ['fallback' => 0.5],
                0,
            ],
            'an invalid positive quality with a valid fallback results in the provided default' => [
                2,
                ['fallback' => 0.5],
                0,
            ],
            'an empty quality with a fallback 0 results in the minimum fallback provided fallback' => [
                '',
                ['fallback' => 0],
                0,
            ],
            'an empty quality with a fallback 0.5 results in the provided fallback' => [
                '',
                ['fallback' => 0.5],
                0.5,
            ],
            'an empty quality with a fallback 1 results in the provided fallback' => [
                '',
                ['fallback' => 1],
                1,
            ],
            'a valid quality 0 with a valid fallback results in the the not acceptable' => [
                0,
                ['fallback' => 1],
                0,
            ],
            'a valid quality 0.3 with a valid fallback results in the quality' => [
                0.3,
                ['fallback' => 0.5],
                0.3,
            ],
            'a valid quality 1 with a valid fallback results in the quality' => [
                1,
                ['fallback' => 0.5],
                1,
            ],
        ];
    }

    #[Test]
    #[DataProvider('provideDifferentAllowEmptyOptions')]
    public function it_can_normalize_an_empty_quality_with_different_options(
        string $quality,
        array $options,
        $expected
    ): void {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertSame($expected, $normalizer->normalize($quality, $options));
    }

    public static function provideDifferentAllowEmptyOptions(): array
    {
        return [
            'an empty quality with the allowed option set to true results in the provided fallback' => [
                '',
                [
                    'fallback' => 0.5,
                    'allow_empty' => true,
                ],
                0.5,
            ],
            'an empty quality with the allowed options set to false results in the not acceptable' => [
                '',
                [
                    'fallback' => 0.5,
                    'allow_empty' => false,
                ],
                0,
            ],
        ];
    }

    #[Test]
    #[DataProvider('providedDifferentQualityBoundaryValues')]
    public function it_can_normalize_at_boundaries($quality, $expected): void
    {
        $normalizer = new LanguageQualityNormalizer();

        $this->assertSame($expected, $normalizer->normalize($quality));
    }

    public static function providedDifferentQualityBoundaryValues(): array
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
