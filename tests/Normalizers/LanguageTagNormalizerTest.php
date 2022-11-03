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
            'an empty string result in no change' => [
                '',
                '',
            ],
            'a space symbol result in no change' => [
                ' ',
                ' ',
            ],
            'a wildcard symbol result in no change' => [
                '*',
                '*',
            ],
            'a two-letter primary result in no change' => [
                'en',
                'en',
            ],
            'a three-letter primary result in no change' => [
                'dum',
                'dum',
            ],
            'a two-letter primary with extlang result in remove extlang' => [
                'zh-yue',
                'zh',
            ],
            'a two-letter primary with script result in no change' => [
                'sr-Latn',
                'sr-Latn',
            ],
            'a two-letter primary with region result in no change' => [
                'de-AT',
                'de-AT',
            ],
            'a two-letter primary with region formatted in digits result in remove region' => [
                'es-005',
                'es',
            ],
            'a two-letter primary with extlang and region result in append only region' => [
                'zh-cmn-CN',
                'zh-CN',
            ],
            'a two-letter primary with script and region result in append script and region' => [
                'sr-Latn-RS',
                'sr-Latn-RS',
            ],
            'a two-letter primary with extlang, script, and region result in append expected only' => [
                'zh-yue-Hant-CN',
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
            'a two-letter primary result in no change' => [
                'en',
                [
                    'separator' => '_',
                ],
                'en',
            ],
            'a two-letter primary with script with hyphen separator result in no change' => [
                'sr-Latn',
                [
                    'separator' => '-',
                ],
                'sr-Latn',
            ],
            'a two-letter primary with script and region with hyphen separator result in no change' => [
                'sr-Latn-RS',
                [
                    'separator' => '-',
                ],
                'sr-Latn-RS',
            ],
            'a two-letter primary with extlang, script, and region with hyphen separator result in no change' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '-',
                ],
                'zh-Hant-CN',
            ],
            'a three-letter primary with script with hyphen separator result in no change' => [
                'sgn-Latn',
                [
                    'separator' => '-',
                ],
                'sgn-Latn',
            ],
            'a two-letter primary with script with underscore separator change the separator' => [
                'sr-Latn',
                [
                    'separator' => '_',
                ],
                'sr_Latn',
            ],
            'a two-letter primary with script and region with underscore separator change the separator' => [
                'sr-Latn-RS',
                [
                    'separator' => '_',
                ],
                'sr_Latn_RS',
            ],
            'a two-letter primary with extlang, script, and region with underscore separator change the separator' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '_',
                ],
                'zh_Hant_CN',
            ],
            'a three-letter primary with script with underscore separator no change' => [
                'sgn-Latn',
                [
                    'separator' => '_',
                ],
                'sgn_Latn',
            ],
            'a two-letter primary with script with tilde separator change the separator' => [
                'sr-Latn',
                [
                    'separator' => '~',
                ],
                'sr~Latn',
            ],
            'a two-letter primary with script and region with tilde separator change the separator' => [
                'sr-Latn-RS',
                [
                    'separator' => '~',
                ],
                'sr~Latn~RS',
            ],
            'a two-letter primary with extlang, script, and region with tilde separator change the separator' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '~',
                ],
                'zh~Hant~CN',
            ],
            'a three-letter primary with script with tilde separator no change' => [
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
        $normalizer = new LanguageTagNormalizer($options);

        $this->assertSame($expected, $normalizer->normalize($tag));
    }

    public function provideLanguageTagsWithDifferentWithOptions(): array
    {
        return [
            'returns a language tag without extlang and with script and region by default' => [
                'zh-yue-Hant-CN',
                [],
                'zh-Hant-CN',
            ],
            'returns a language tag with extlang' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                ],
                'zh-yue-Hant-CN',
            ],
            'returns a language tag without script' => [
                'zh-yue-Hant-CN',
                [
                    'with_script' => false,
                ],
                'zh-CN',
            ],
            'returns a language tag without region' => [
                'zh-yue-Hant-CN',
                [
                    'with_region' => false,
                ],
                'zh-Hant',
            ],
            'returns expected language tag with all switched on' => [
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                    'with_script' => true,
                    'with_region' => true,
                ],
                'zh-yue-Hant-CN',
            ],
            'returns expected language tag with all switched off' => [
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
            'a two-letter tag BCP47 section 2.1.1 example 1 return formatted' => [
                'mn-Cyrl-MN',
                'mn-Cyrl-MN',
            ],
            'a two-letter tag BCP47 section 2.1.1 example 2 return formatted' => [
                'MN-cYRL-mn',
                'mn-Cyrl-MN',
            ],
            'a two-letter tag BCP47 section 2.1.1 example 3 return formatted' => [
                'mN-cYrL-Mn',
                'mn-Cyrl-MN',
            ],
        ];
    }
}
