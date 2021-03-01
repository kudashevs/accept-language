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
            'two-letter primary hyphenated with extlang remove extlang' => [
                'zh',
                'zh-yue',
            ],
            'two-letter primary underscored with extlang remove extlang' => [
                'zh',
                'zh_yue',
            ],
            'two-letter primary hyphenated with script append script' => [
                'sr_Latn',
                'sr-Latn',
            ],
            'two-letter primary underscored with script append script' => [
                'sr_Latn',
                'sr_Latn',
            ],
            'two-letter primary hyphenated with region append region' => [
                'de_AT',
                'de-AT',
            ],
            'two-letter primary underscored with region append region' => [
                'de_AT',
                'de_AT',
            ],
            'two-letter primary hyphenated with extlang and region append only region' => [
                'zh_CN',
                'zh-cmn-CN',
            ],
            'two-letter primary underscored with extlang and region append only region' => [
                'zh_CN',
                'zh_cmn_CN',
            ],
            'two-letter primary hyphenated with script and region append both' => [
                'sr_Latn_RS',
                'sr-Latn-RS',
            ],
            'two-letter primary underscored with script and region append both' => [
                'sr_Latn_RS',
                'sr_Latn_RS',
            ],
            'two-letter primary hyphenated with extlang, script, and region append expected' => [
                'zh_Hant_CN',
                'zh-yue-Hant-CN',
            ],
            'two-letter primary underscored with extlang, script, and region append expected' => [
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
            'two-letter primary underscored with extlang out of its scope' => [
                'zh_CN',
                'zh-cmn-CN-cmn',
            ],
            'two-letter primary underscored with script out of its scope' => [
                'zh_CN',
                'zh-cmn-CN-Latn',
            ],
        ];
    }
}
