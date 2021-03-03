<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    const DEFAULT_LANGUAGE = 'en';

    /**
     * @dataProvider provideWrongOptions
     */
    public function testSetOptionsThrowsExceptionOnWrongOptionParameterType($option)
    {
        $this->expectException(InvalidOptionArgumentException::class);
        $this->expectExceptionMessage('The option ' . key($option) . ' has a wrong value type');

        new AcceptLanguage($option);
    }

    public function provideWrongOptions()
    {
        return [
            'wrong http_accept_language without string value' => [
                [
                    'http_accept_language' => null,
                ],
            ],
            'wrong default_language without not string value' => [
                [
                    'default_language' => null,
                ],
            ],
            'wrong accepted_languages without not array value' => [
                [
                    'accepted_languages' => null,
                ],
            ],
        ];
    }

    public function testGetPreferredLanguageReturnsNotEmpty()
    {
        $service = new AcceptLanguage();

        $this->assertNotEmpty($service->getPreferredLanguage());
    }

    public function testGetPreferredLanguageReturnsDefaultLanguageWhenLanguageInformationIsEmptyAndHTTPAcceptLanguageIsNotAccessible()
    {
        $service = new AcceptLanguage();

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
    }

    public function testGetPreferredLanguageReturnsDefaultLanguageFromOptionsWhenLanguageInformationIsEmptyAndHTTPAcceptLanguageIsNotAccessible()
    {
        $options = ['default_language' => 'de'];
        $service = new AcceptLanguage($options);

        $this->assertSame($options['default_language'], $service->getPreferredLanguage());
    }

    public function testGetPreferredLanguageReturnsDefaultLanguageWhenRetrievedLanguageIsNotListedInAcceptedLanguages()
    {
        $options = [
            'http_accept_language' => 'pp',
            'accepted_languages' => ['en', 'de', 'fr']
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
    }

    public function testGetPreferredLanguageReturnsDefaultLanguageFromOptionsWhenAcceptedLanguagesContainsOptionalLanguage()
    {
        $options = [
            'default_language' => 'es',
            'accepted_languages' => ['en', 'de', 'es'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame($options['default_language'], $service->getPreferredLanguage());
    }

    /**
     * @dataProvider provideHeaderValue
     */
    public function testGetPreferredLanguageReturnsExpected($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideHeaderValue()
    {
        return [
            'any language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => '*'],
            ],
            'any language tag with highest quality results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => '*,de;q=0.7'],
            ],
            'any language tag and language tag with equal quality results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => '*,es,de;q=0.7'],
            ],
            'any language tag and language tag with equal quality results language' => [
                'es',
                ['http_accept_language' => 'es,*,de;q=0.7'],
            ],
            'two-letter primary language tag results language' => [
                'en',
                ['http_accept_language' => 'en'],
            ],
            'three-letter primary language tag results language' => [
                'sgn',
                ['http_accept_language' => 'sgn'],
            ],
            'four letters primary language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'test'],
            ],
            'two-letter with 0 quality language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'de;q=0'],
            ],
            'three-letter with 0 quality language tag results language' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn;q=0'],
            ],
            'two-letter with 0.001 quality language tag results default' => [
                'de',
                ['http_accept_language' => 'de;q=0.001'],
            ],
            'three-letter with 0.001 quality language tag results language' => [
                'sgn',
                ['http_accept_language' => 'sgn;q=0.001'],
            ],
            'two-letter with quality language tag results language' => [
                'de',
                ['http_accept_language' => 'de;q=0.5'],
            ],
            'three-letter with quality language tag results language' => [
                'sgn',
                ['http_accept_language' => 'sgn;q=0.5'],
            ],
            'two-letter with 0.999 quality language tag results default' => [
                'de',
                ['http_accept_language' => 'de;q=0.999'],
            ],
            'three-letter with 0.999 quality language tag results language' => [
                'sgn',
                ['http_accept_language' => 'sgn;q=0.999'],
            ],
            'two-letter with 1 quality language tag results default' => [
                'de',
                ['http_accept_language' => 'de;q=1'],
            ],
            'three-letter with 1 quality language tag results language' => [
                'sgn',
                ['http_accept_language' => 'sgn;q=1'],
            ],
            'two-letter with 1.001 quality language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'de;q=1.001'],
            ],
            'three-letter with 1.001 quality language tag results language' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn;q=1.001'],
            ],
            'four letters with quality language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'test;q=0.5'],
            ],
            'sequence of primary tags results language' => [
                'de',
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
            ],
            'example all lowercase results language' => [
                'de',
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
            ],
            'example part uppercase results language' => [
                'de_DE',
                ['http_accept_language' => 'de-DE,de;q=0.9,en;q=0.8'],
            ],
            'mozilla Accept-Language page examples basic results language' => [
                'de',
                ['http_accept_language' => 'de'],
            ],
            'mozilla Accept-Language page examples hyphenated results language' => [
                'de_CH',
                ['http_accept_language' => 'de-CH'],
            ],
            'mozilla Accept-Language page examples complex results language' => [
                'en_US',
                ['http_accept_language' => 'en-US,en;q=0.5'],
            ],
            'mozilla Accept-Language page examples complex with space results language' => [
                'fr_CH',
                ['http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'],
            ],
            'RFC 2616 14.4 Accept-Language example results language' => [
                'da',
                ['http_accept_language' => 'da, en-gb;q=0.8, en;q=0.7'],
            ],
        ];
    }

    /**
     * @dataProvider provideHeaderValueAndAcceptedLanguagesOption
     */
    public function testGetPreferredLanguageReturnsExpectedWhenAcceptedLanguagesAreSet($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideHeaderValueAndAcceptedLanguagesOption()
    {
        return [
            'language that intersects with accepted_languages results language' => [
                'de',
                [ 'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5', 'accepted_languages' => ['de'], ],
            ],
            'language that intersects with accepted_languages results language when it is of quality 1' => [
                'fr',
                [ 'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5', 'accepted_languages' => ['de', 'fr'], ],
            ],
            'language that intersects with accepted_languages results language when it is of quality below 1' => [
                'es',
                [ 'http_accept_language' => 'de;q=0.7,fr;q=0.333,es;q=0.333', 'accepted_languages' => ['en', 'es'], ],
            ],
            'RFC 2616 14.4 Accept-Language example returns accepted language when it is of quality 1' => [
                'en',
                [ 'http_accept_language' => 'da, en-gb, fr;q=0.8, en;q=0.7', 'accepted_languages' => ['en'], ],
            ],
            'RFC 2616 14.4 Accept-Language example returns accepted language when it is of quality below 1' => [
                'fr',
                [ 'http_accept_language' => 'da, en-gb, fr;q=0.8, en;q=0.7', 'accepted_languages' => ['fr'], ],
            ],
        ];
    }

    /**
     * @dataProvider provideIncorrectOrWronglyFormedHeaderValue
     */
    public function testGetPreferredLanguageProcessesIncorrectOrWronglyFormedHeaderValue($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideIncorrectOrWronglyFormedHeaderValue()
    {
        return [
            'one empty language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => ''],
            ],
            'two empty languages tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => ','],
            ],
            'two empty languages with quality tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => ',;q='],
            ],
            'one language with wrong quality digit tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'es;q=5'],
            ],
            'one language with wrong quality value tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'es;q=dd'],
            ],
            'two language with wrong quality order result highest' => [
                'es',
                ['http_accept_language' => 'en;q=0.3,es;q=1'],
            ],
        ];
    }

    /**
     * @dataProvider provideHeaderValueForNormalization
     */
    public function testGetPreferredLanguageReturnsNormalized($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideHeaderValueForNormalization()
    {
        return [
            'language with hyphen intersects with hyphenated accepted_languages once results language' => [
                'zh_Hant_HK',
                [ 'http_accept_language' => 'zH-HanT-Hk, en;q=0.9, *;q=0.5', 'accepted_languages' => ['zh-Hant-HK'], ],
            ],
            'language with hyphen intersects with underscored accepted_languages once results language' => [
                'zh_Hant_HK',
                [ 'http_accept_language' => 'zH-HanT-Hk, en;q=0.9, *;q=0.5', 'accepted_languages' => ['zh_Hant_HK'], ],
            ],
            'language with underscore intersects with hyphenated accepted_languages once results language' => [
                'zh_Hant_HK',
                [ 'http_accept_language' => 'zH_HanT_Hk, en;q=0.9, *;q=0.5', 'accepted_languages' => ['zh-Hant-HK'], ],
            ],
            'language with underscore intersects with underscored accepted_languages once results language' => [
                'zh_Hant_HK',
                [ 'http_accept_language' => 'zH_HanT_Hk, en;q=0.9, *;q=0.5', 'accepted_languages' => ['zh_Hant_HK'], ],
            ],
        ];
    }

    /**
     * Caught bugs.
     */
    public function testGetPreferredLanguageCaughtIntersectionBugInRetrieveIntersectionWithAcceptableLanguages()
    {
        $options = [
            'http_accept_language' => 'fr-CH,fr;q=0.8,en-US;q=0.5,en;q=0.3',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('fr', $service->getPreferredLanguage());
    }
}
