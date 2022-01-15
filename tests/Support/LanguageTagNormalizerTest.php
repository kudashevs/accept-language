<?php

namespace Kudashevs\AcceptLanguage\Tests\Support;

use Kudashevs\AcceptLanguage\Support\LanguageTagNormalizer;
use PHPUnit\Framework\TestCase;

class LanguageTagNormalizerTest extends TestCase
{
    public function testNormalizerReturnsNonEmpty()
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertNotEmpty($normalizer->normalize('en'));
    }

    /**
     * @dataProvider provideLanguageTag
     * @param string $expected
     * @param string $raw
     */
    public function testNormalizerReturnsNormalizedLanguageTag(string $expected, string $raw)
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertSame($expected, $normalizer->normalize($raw));
    }

    public function provideLanguageTag()
    {
        return [
            'two-letter primary without change' => [
                'en',
                'en',
            ],
            'three-letter primary without change' => [
                'dum',
                'dum',
            ],
            'two-letter tag hyphenated with extlang remove extlang' => [
                'zh',
                'zh-yue',
            ],
            'two-letter tag underscored with extlang remove extlang' => [
                'zh',
                'zh_yue',
            ],
            'two-letter tag hyphenated with script append script' => [
                'sr-Latn',
                'sr-Latn',
            ],
            'two-letter tag underscored with script append script' => [
                'sr-Latn',
                'sr_Latn',
            ],
            'two-letter tag hyphenated with region append region' => [
                'de-AT',
                'de-AT',
            ],
            'two-letter tag underscored with region append region' => [
                'de-AT',
                'de_AT',
            ],
            'two-letter tag hyphenated with region in digits remove region' => [
                'es',
                'es-005',
            ],
            'two-letter tag underscored with region in digits remove region' => [
                'es',
                'es-005',
            ],
            'two-letter tag hyphenated with extlang and region append only region' => [
                'zh-CN',
                'zh-cmn-CN',
            ],
            'two-letter tag underscored with extlang and region append only region' => [
                'zh-CN',
                'zh_cmn_CN',
            ],
            'two-letter tag hyphenated with script and region append both' => [
                'sr-Latn-RS',
                'sr-Latn-RS',
            ],
            'two-letter tag underscored with script and region append both' => [
                'sr-Latn-RS',
                'sr_Latn_RS',
            ],
            'two-letter tag hyphenated with extlang, script, and region append expected' => [
                'zh-Hant-CN',
                'zh-yue-Hant-CN',
            ],
            'two-letter tag underscored with extlang, script, and region append expected' => [
                'zh-Hant-CN',
                'zh_yue_Hant_CN',
            ],
        ];
    }

    /**
     * @dataProvider provideLanguageTagWithDifferentSeparatorOption
     * @param string $expected
     * @param string $raw
     * @param array $options
     */
    public function testNormalizerAcceptsDifferentSeparators(string $expected, string $raw, array $options)
    {
        $normalizer = new LanguageTagNormalizer($options);

        $this->assertSame($expected, $normalizer->normalize($raw));
    }

    public function provideLanguageTagWithDifferentSeparatorOption()
    {
        return [
            'two-letter primary without change' => [
                'en',
                'en',
                [
                    'separator' => '_',
                ],
            ],
            'two-letter tag hyphenated with script change separator' => [
                'sr_Latn',
                'sr-Latn',
                [
                    'separator' => '_',
                ],
            ],
            'two-letter tag hyphenated with script and region change separator' => [
                'sr_Latn_RS',
                'sr-Latn-RS',
                [
                    'separator' => '_',
                ],
            ],
            'two-letter tag hyphenated with extlang, script, and region change separator' => [
                'zh_Hant_CN',
                'zh-yue-Hant-CN',
                [
                    'separator' => '_',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideLanguageTagWithDifferentWithOptions
     * @param string $expected
     * @param string $raw
     * @param array $options
     */
    public function testNormalizerReturnsExpectedWithSpecificOptionSet(string $expected, string $raw, array $options)
    {
        $normalizer = new LanguageTagNormalizer($options);

        $this->assertSame($expected, $normalizer->normalize($raw));
    }

    public function provideLanguageTagWithDifferentWithOptions()
    {
        return [
            'returns without extlang and with script and region by default' => [
                'zh-Hant-CN',
                'zh-yue-Hant-CN',
                [],
            ],
            'returns with extlang' => [
                'zh-yue-Hant-CN',
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                ],
            ],
            'returns without script' => [
                'zh-CN',
                'zh-yue-Hant-CN',
                [
                    'with_script' => false,
                ],
            ],
            'returns without region' => [
                'zh-Hant',
                'zh-yue-Hant-CN',
                [
                    'with_region' => false,
                ],
            ],
            'returns expected with all switched on' => [
                'zh-yue-Hant-CN',
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => true,
                    'with_script' => true,
                    'with_region' => true,
                ],
            ],
            'returns expected with all switched off' => [
                'zh',
                'zh-yue-Hant-CN',
                [
                    'with_extlang' => false,
                    'with_script' => false,
                    'with_region' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideExceptionalCases
     * @param string $expected
     * @param string $raw
     */
    public function testNormalizerReturnsNormalizedLanguageTagOnExceptionalCase(string $expected, string $raw)
    {
        $normalizer = new LanguageTagNormalizer();

        $this->assertSame($expected, $normalizer->normalize($raw));
    }

    public function provideExceptionalCases()
    {
        return [
            'two-letter tag hyphenated with extlang out of its scope' => [
                'zh-CN',
                'zh-cmn-CN-cmn',
            ],
            'two-letter tag hyphenated with script out of its scope' => [
                'zh-CN',
                'zh-cmn-CN-Latn',
            ],
            'two-letter tag hyphenated with region out of its scope' => [
                'de',
                'de-ext-ext-Latn-CH-1901',
            ],
            'two-letter tag BCP47 section 2.1.1 example 1 return formatted' => [
                'mn-Cyrl-MN',
                'mn-Cyrl-MN',
            ],
            'two-letter tag BCP47 section 2.1.1 example 2 return formatted' => [
                'mn-Cyrl-MN',
                'MN-cYRL-mn',
            ],
            'two-letter tag BCP47 section 2.1.1 example 3 return formatted' => [
                'mn-Cyrl-MN',
                'mN-cYrL-Mn',
            ],
        ];
    }
}
