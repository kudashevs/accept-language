<?php

namespace Kudashevs\AcceptLanguage\Tests\Normalizers;

use Kudashevs\AcceptLanguage\Normalizers\LanguageTagNormalizer;
use PHPUnit\Framework\TestCase;

class LanguageTagNormalizerTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertNotEmpty($normalizer->normalize('en'));
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageTags
     */
    public function it_can_normalize_a_language_tag(string $tag, string $expected)
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertSame($expected, $normalizer->normalize($tag));
    }

    public function provideDifferentLanguageTags(): array
    {
        return [
            'empty string without change' => [
                '',
                '',
            ],
            'space symbol without change' => [
                ' ',
                ' ',
            ],
            'wildcard symbol without change' => [
                '*',
                '*',
            ],
            'two-letter primary without change' => [
                'en',
                'en',
            ],
            'three-letter primary without change' => [
                'dum',
                'dum',
            ],
            'two-letter tag hyphenated with extlang remove extlang' => [
                'zh-yue',
                'zh',
            ],
            'two-letter tag underscored with extlang remove extlang' => [
                'zh_yue',
                'zh',
            ],
            'two-letter tag hyphenated with script append script' => [
                'sr-Latn',
                'sr-Latn',
            ],
            'two-letter tag underscored with script append script' => [
                'sr_Latn',
                'sr-Latn',
            ],
            'two-letter tag hyphenated with region append region' => [
                'de-AT',
                'de-AT',
            ],
            'two-letter tag underscored with region append region' => [
                'de_AT',
                'de-AT',
            ],
            'two-letter tag hyphenated with region in digits remove region' => [
                'es-005',
                'es',
            ],
            'two-letter tag underscored with region in digits remove region' => [
                'es-005',
                'es',
            ],
            'two-letter tag hyphenated with extlang and region append only region' => [
                'zh-cmn-CN',
                'zh-CN',
            ],
            'two-letter tag underscored with extlang and region append only region' => [
                'zh_cmn_CN',
                'zh-CN',
            ],
            'two-letter tag hyphenated with script and region append both' => [
                'sr-Latn-RS',
                'sr-Latn-RS',
            ],
            'two-letter tag underscored with script and region append both' => [
                'sr_Latn_RS',
                'sr-Latn-RS',
            ],
            'two-letter tag hyphenated with extlang, script, and region append expected' => [
                'zh-yue-Hant-CN',
                'zh-Hant-CN',
            ],
            'two-letter tag underscored with extlang, script, and region append expected' => [
                'zh_yue_Hant_CN',
                'zh-Hant-CN',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideLanguageTagsWithDifferentSeparatorOption
     */
    public function it_can_normalize_with_a_provided_separator(string $tag, array $options, string $expected)
    {
        $normalizer = new LanguageTagNormalizer($options);

        $this->assertSame($expected, $normalizer->normalize($tag));
    }

    public function provideLanguageTagsWithDifferentSeparatorOption(): array
    {
        return [
            'two-letter primary without change' => [
                'en',
                [
                    'separator' => '_',
                ],
                'en',
            ],
            'two-letter tag hyphenated with script change separator' => [
                'sr-Latn',
                [
                    'separator' => '_',
                ],
                'sr_Latn',
            ],
            'two-letter tag hyphenated with script and region change separator' => [
                'sr-Latn-RS',
                [
                    'separator' => '_',
                ],
                'sr_Latn_RS',
            ],
            'two-letter tag hyphenated with extlang, script, and region change separator' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '_',
                ],
                'zh_Hant_CN',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideLanguageTagsWithDifferentWithOptions
     */
    public function it_can_normalize_with_provided_options(string $tag, array $options, string $expected)
    {
        $normalizer = new LanguageTagNormalizer($options);

        $this->assertSame($expected, $normalizer->normalize($tag));
    }

    public function provideLanguageTagsWithDifferentWithOptions(): array
    {
        return [
            'returns without extlang and with script and region by default' => [
                'zh-yue-Hant-CN',
                [],
                'zh-Hant-CN',
            ],
            'returns with extlang' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                ],
                'zh-yue-Hant-CN',
            ],
            'returns without script' => [
                'zh-yue-Hant-CN',
                [
                    'with_script' => false,
                ],
                'zh-CN',
            ],
            'returns without region' => [
                'zh-yue-Hant-CN',
                [
                    'with_region' => false,
                ],
                'zh-Hant',
            ],
            'returns expected with all switched on' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                    'with_script' => true,
                    'with_region' => true,
                ],
                'zh-yue-Hant-CN',
            ],
            'returns expected with all switched off' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => false,
                    'with_script' => false,
                    'with_region' => false,
                ],
                'zh',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideExceptionalCases
     */
    public function it_can_normalize_an_exceptional_case(string $tag, string $expected)
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertSame($expected, $normalizer->normalize($tag));
    }

    public function provideExceptionalCases(): array
    {
        return [
            'two-letter tag hyphenated with extlang out of its scope' => [
                'zh-cmn-CN-cmn',
                'zh-CN',
            ],
            'two-letter tag hyphenated with script out of its scope' => [
                'zh-cmn-CN-Latn',
                'zh-CN',
            ],
            'two-letter tag hyphenated with region out of its scope' => [
                'de-ext-ext-Latn-CH-1901',
                'de',
            ],
            'two-letter tag BCP47 section 2.1.1 example 1 return formatted' => [
                'mn-Cyrl-MN',
                'mn-Cyrl-MN',
            ],
            'two-letter tag BCP47 section 2.1.1 example 2 return formatted' => [
                'MN-cYRL-mn',
                'mn-Cyrl-MN',
            ],
            'two-letter tag BCP47 section 2.1.1 example 3 return formatted' => [
                'mN-cYrL-Mn',
                'mn-Cyrl-MN',
            ],
        ];
    }
}
