<?php

namespace Kudashevs\AcceptLanguage\Tests\Support;

use Kudashevs\AcceptLanguage\Support\LanguageTag;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    public function testNormalizeReturnsNotEmpty()
    {
        $languageTag = new LanguageTag();

        $this->assertNotEmpty($languageTag->normalize('en'));
    }

    /**
     * @dataProvider provideLanguageTag
     */
    public function testNormalizeReturnsNormalizedLanguageTag($expected, $raw)
    {
        $languageTag = new LanguageTag();

        $this->assertSame($expected, $languageTag->normalize($raw));
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
                'sr_Latn',
                'sr-Latn',
            ],
            'two-letter tag underscored with script append script' => [
                'sr_Latn',
                'sr_Latn',
            ],
            'two-letter tag hyphenated with region append region' => [
                'de_AT',
                'de-AT',
            ],
            'two-letter tag underscored with region append region' => [
                'de_AT',
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
                'zh_CN',
                'zh-cmn-CN',
            ],
            'two-letter tag underscored with extlang and region append only region' => [
                'zh_CN',
                'zh_cmn_CN',
            ],
            'two-letter tag hyphenated with script and region append both' => [
                'sr_Latn_RS',
                'sr-Latn-RS',
            ],
            'two-letter tag underscored with script and region append both' => [
                'sr_Latn_RS',
                'sr_Latn_RS',
            ],
            'two-letter tag hyphenated with extlang, script, and region append expected' => [
                'zh_Hant_CN',
                'zh-yue-Hant-CN',
            ],
            'two-letter tag underscored with extlang, script, and region append expected' => [
                'zh_Hant_CN',
                'zh_yue_Hant_CN',
            ],
        ];
    }

    /**
     * @dataProvider provideExceptionalCase
     */
    public function testGetNormalizeReturnsNormalizedLanguageTagOnExceptionalCase($expected, $raw)
    {
        $languageTag = new LanguageTag();

        $this->assertSame($expected, $languageTag->normalize($raw));
    }

    public function provideExceptionalCase()
    {
        return [
            'two-letter tag hyphenated with extlang out of its scope' => [
                'zh_CN',
                'zh-cmn-CN-cmn',
            ],
            'two-letter tag hyphenated with script out of its scope' => [
                'zh_CN',
                'zh-cmn-CN-Latn',
            ],
            'two-letter tag hyphenated with region out of its scope' => [
                'de',
                'de-ext-ext-Latn-CH-1901',
            ],
        ];
    }
}
