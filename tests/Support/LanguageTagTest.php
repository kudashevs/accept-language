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
    public function testGetPreferredLanguageReturnsNormalizedLanguageTag($expected, $raw)
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
            'two-letter hyphenated with extlang remove extlang' => [
                'zh',
                'zh-yue',
            ],
            'two-letter underscored with extlang remove extlang' => [
                'zh',
                'zh_yue',
            ],
            'two-letter hyphenated with script append script' => [
                'sr_Latn',
                'sr-Latn',
            ],
            'two-letter underscored with script append script' => [
                'sr_Latn',
                'sr_Latn',
            ],
        ];
    }
}
