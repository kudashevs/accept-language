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
    public function testGetPreferredLanguageReturnsNormalizedLanguageTag($raw, $expected)
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
                'zh-yue',
                'zh',
            ],
            'two-letter underscored with extlang remove extlang' => [
                'zh_yue',
                'zh',
            ],
        ];
    }
}
