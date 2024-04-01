<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Normalizers;

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

    public static function provideDifferentLanguageTags(): array
    {
        return [
            'an empty string results in no change' => [
                '',
                '',
            ],
            'a space symbol results in no change' => [
                ' ',
                ' ',
            ],
            'a wildcard symbol results in no change' => [
                '*',
                '*',
            ],
            'a one-letter language tag results in no change' => [
                'a',
                'a',
            ],
            'a two-letter language tag results in no change' => [
                'en',
                'en',
            ],
            'a three-letter language tag results in no change' => [
                'dum',
                'dum',
            ],
            'an eight-letter language tag results in no change' => [
                'enochian',
                'enochian',
            ],
            'a two-letter language tag with extlang results in remove extlang' => [
                'zh-yue',
                'zh',
            ],
            'a two-letter language tag with script results in no change' => [
                'sr-Latn',
                'sr-Latn',
            ],
            'a two-letter language tag with region results in no change' => [
                'de-AT',
                'de-AT',
            ],
            'a two-letter language tag with region in digits results in no change' => [
                'es-005',
                'es-005',
            ],
            'a two-letter language tag with extlang and region results in append only region' => [
                'zh-cmn-CN',
                'zh-CN',
            ],
            'a two-letter language tag with script and region results in append script and region' => [
                'sr-Latn-RS',
                'sr-Latn-RS',
            ],
            'a two-letter language tag with extlang, script, and region results in append only script and region' => [
                'zh-yue-Hant-CN',
                'zh-Hant-CN',
            ],
            'a two-letter language tag with variant results in remove variant' => [
                'sl-nedis',
                'sl',
            ],
            'a two-letter language tag with region and variant results in remove variant' => [
                'sl-IT-nedis',
                'sl-IT',
            ],
            'a two-letter language tag with region and variant in digits results in remove variant' => [
                'de-CH-1901',
                'de-CH',
            ],
            'a two-letter language tag with region and extended variant results in remove variant' => [
                'fr-FR-1694acad',
                'fr-FR',
            ],
            'a two-letter language tag with region and extension results in remove extension' => [
                'de-DE-u-co-phonebk',
                'de-DE',
            ],
            'a two-letter language tag with region and private results in remove private' => [
                'en-US-x-twain',
                'en-US',
            ],
            'a two-letter language tag with extlange, script, region, variant, extension and private-use subtags results in the expected' => [
                'th-tsq-thai-th-bauddha-t-th-x-foobar-private',
                'th-Thai-TH',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideLanguageTagsWithDifferentSeparatorOption
     */
    public function it_can_normalize_with_a_provided_separator(string $tag, array $options, string $expected)
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertSame($expected, $normalizer->normalize($tag, $options));
    }

    public static function provideLanguageTagsWithDifferentSeparatorOption(): array
    {
        return [
            'a two-letter language tag results in no change' => [
                'en',
                [
                    'separator' => '_',
                ],
                'en',
            ],
            'a two-letter language tag with script with hyphen separator results in no change' => [
                'sr-Latn',
                [
                    'separator' => '-',
                ],
                'sr-Latn',
            ],
            'a two-letter language tag with script and region with hyphen separator results in no change' => [
                'sr-Latn-RS',
                [
                    'separator' => '-',
                ],
                'sr-Latn-RS',
            ],
            'a two-letter language tag with extlang, script, and region with hyphen separator results in no change' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '-',
                ],
                'zh-Hant-CN',
            ],
            'a three-letter language tag with script with hyphen separator results in no change' => [
                'sgn-Latn',
                [
                    'separator' => '-',
                ],
                'sgn-Latn',
            ],
            'a two-letter language tag with script with underscore separator change the separator' => [
                'sr-Latn',
                [
                    'separator' => '_',
                ],
                'sr_Latn',
            ],
            'a two-letter language tag with script and region with underscore separator change the separator' => [
                'sr-Latn-RS',
                [
                    'separator' => '_',
                ],
                'sr_Latn_RS',
            ],
            'a two-letter language tag with extlang, script, and region with underscore separator change the separator' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '_',
                ],
                'zh_Hant_CN',
            ],
            'a three-letter language tag with script with underscore separator no change' => [
                'sgn-Latn',
                [
                    'separator' => '_',
                ],
                'sgn_Latn',
            ],
            'a two-letter language tag with script with tilde separator change the separator' => [
                'sr-Latn',
                [
                    'separator' => '~',
                ],
                'sr~Latn',
            ],
            'a two-letter language tag with script and region with tilde separator change the separator' => [
                'sr-Latn-RS',
                [
                    'separator' => '~',
                ],
                'sr~Latn~RS',
            ],
            'a two-letter language tag with extlang, script, and region with tilde separator change the separator' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '~',
                ],
                'zh~Hant~CN',
            ],
            'a three-letter language tag with script with tilde separator no change' => [
                'sr-Latn',
                [
                    'separator' => '~',
                ],
                'sr~Latn',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideLanguageTagsWithDifferentWithOptions
     */
    public function it_can_normalize_with_provided_options(string $tag, array $options, string $expected)
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertSame($expected, $normalizer->normalize($tag, $options));
    }

    public static function provideLanguageTagsWithDifferentWithOptions(): array
    {
        return [
            'returns a language tag without extlang, variant, extension and private-use subtags by default' => [
                'zh-yue-Hant-CN-tongyong-u-co-phonebk-x-foobar',
                [],
                'zh-Hant-CN',
            ],
            'returns a language tag with extlang when extlang option set to true' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                ],
                'zh-yue-Hant-CN',
            ],
            'returns a language tag without extlang when extlang option set to false' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => false,
                ],
                'zh-Hant-CN',
            ],
            'returns a language tag with script when script option set to true' => [
                'zh-yue-Hant-CN',
                [
                    'with_script' => true,
                ],
                'zh-Hant-CN',
            ],
            'returns a language tag without script when script option set to false' => [
                'zh-yue-Hant-CN',
                [
                    'with_script' => false,
                ],
                'zh-CN',
            ],
            'returns a language tag with region when region option set to true' => [
                'zh-yue-Hant-CN',
                [
                    'with_region' => true,
                ],
                'zh-Hant-CN',
            ],
            'returns a language tag without region when region option set to false' => [
                'zh-yue-Hant-CN',
                [
                    'with_region' => false,
                ],
                'zh-Hant',
            ],
            'returns a language tag with region in digits when region option set to true' => [
                'zh-yue-Hant-005',
                [
                    'with_region' => true,
                ],
                'zh-Hant-005',
            ],
            'returns a language tag without region in digits when region option set to false' => [
                'zh-yue-Hant-005',
                [
                    'with_region' => false,
                ],
                'zh-Hant',
            ],
            'returns an expected language tag with all switched on' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                    'with_script' => true,
                    'with_region' => true,
                ],
                'zh-yue-Hant-CN',
            ],
            'returns an expected language tag with all switched off' => [
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

    public static function provideExceptionalCases(): array
    {
        return [
            'a two-letter tag RFC 5646 section 2.1.1 case insensitive example 1 returns formatted' => [
                'mn-Cyrl-MN',
                'mn-Cyrl-MN',
            ],
            'a two-letter tag RFC 5646 section 2.1.1 case insensitive example 2 returns formatted' => [
                'MN-cYRL-mn',
                'mn-Cyrl-MN',
            ],
            'a two-letter tag RFC 5646 section 2.1.1 case insensitive example 3 returns formatted' => [
                'mN-cYrL-Mn',
                'mn-Cyrl-MN',
            ],
        ];
    }
}
