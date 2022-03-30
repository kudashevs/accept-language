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

    /**
     * @dataProvider provideGetPreferredLanguageMethods
     */
    public function testMethodReturnsNotEmpty(string $method)
    {
        $service = new AcceptLanguage();

        $this->assertNotEmpty($service->$method());
    }

    /**
     * @dataProvider provideGetPreferredLanguageMethods
     */
    public function testMethodReturnsDefaultLanguageWhenLanguageOptionsAreEmptyAndHTTPAcceptLanguageIsNotAccessible(string $method)
    {
        $service = new AcceptLanguage();

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->$method());
    }

    public function testGetPreferredLanguageReturnsDefaultLanguageFromOptionsWhenLanguageOptionsAreEmptyAndHTTPAcceptLanguageIsNotAccessible()
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

    public function provideGetPreferredLanguageMethods()
    {
        return [
            'with getPreferredLanguage method' => ['getPreferredLanguage'],
            'with getLanguage method' => ['getLanguage'],
        ];
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
                'fr',
                ['http_accept_language' => 'fr'],
            ],
            'three-letter primary language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn'],
            ],
            'three-letter primary language tag with optionresults default' => [
                'sgn',
                [
                    'http_accept_language' => 'sgn',
                    'two_letter_only' => false,
                ],
            ],
            'four letters primary language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'test'],
            ],
            'two-letter primary language tag with region results language' => [
                'en_US',
                ['http_accept_language' => 'en-us'],
            ],
            'two-letter primary language tag with script and region results language' => [
                'zh_Hant_HK',
                ['http_accept_language' => 'zh-Hant-HK'],
            ],
            'two-letter with 0 quality language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'de;q=0'],
            ],
            'three-letter with 0 quality language tag results language' => [
                self::DEFAULT_LANGUAGE,
                [
                    'http_accept_language' => 'sgn;q=0',
                    'two_letter_only' => false,
                ],
            ],
            'two-letter with 0.001 quality language tag results default' => [
                'de',
                ['http_accept_language' => 'de;q=0.001'],
            ],
            'three-letter with 0.001 quality language tag results language' => [
                'sgn',
                [
                    'http_accept_language' => 'sgn;q=0.001',
                    'two_letter_only' => false,
                ],
            ],
            'two-letter with quality language tag results language' => [
                'de',
                ['http_accept_language' => 'de;q=0.5'],
            ],
            'three-letter with quality language tag results language' => [
                'sgn',
                [
                    'http_accept_language' => 'sgn;q=0.5',
                    'two_letter_only' => false,
                ],
            ],
            'two-letter with 0.999 quality language tag results default' => [
                'de',
                ['http_accept_language' => 'de;q=0.999'],
            ],
            'three-letter with 0.999 quality language tag results language' => [
                'sgn',
                [
                    'http_accept_language' => 'sgn;q=0.999',
                    'two_letter_only' => false,
                ],
            ],
            'two-letter with 1 quality language tag results default' => [
                'de',
                ['http_accept_language' => 'de;q=1'],
            ],
            'three-letter with 1 quality language tag results language' => [
                'sgn',
                [
                    'http_accept_language' => 'sgn;q=1',
                    'two_letter_only' => false,
                ],
            ],
            'two-letter with 1.001 quality language tag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'de;q=1.001'],
            ],
            'three-letter with 1.001 quality language tag results language' => [
                self::DEFAULT_LANGUAGE,
                [
                    'http_accept_language' => 'sgn;q=1.001',
                    'two_letter_only' => false,
                ],
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
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['de'],
                ],
            ],
            'language that intersects with accepted_languages results language when it is of quality 1' => [
                'fr',
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['de', 'fr'],
                ],
            ],
            'language that intersects with accepted_languages results language when it is of quality below 1' => [
                'es',
                [
                    'http_accept_language' => 'de;q=0.7,fr;q=0.333,es;q=0.333',
                    'accepted_languages' => ['en', 'es'],
                ],
            ],
            'RFC 2616 14.4 Accept-Language example returns accepted language when it is of quality 1' => [
                'en',
                [
                    'http_accept_language' => 'da, en-gb, fr;q=0.8, en;q=0.7',
                    'accepted_languages' => ['en'],
                ],
            ],
            'RFC 2616 14.4 Accept-Language example returns accepted language when it is of quality below 1' => [
                'fr',
                [
                    'http_accept_language' => 'da, en-gb, fr;q=0.8, en;q=0.7',
                    'accepted_languages' => ['fr'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideHeaderValueWithTheSpecificOption
     */
    public function testGetPreferredLanguageReturnsExpectedWhenSpecificOptionIsSet($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideHeaderValueWithTheSpecificOption()
    {
        return [
            'returns expected with the underscore separator' => [
                'en_GB',
                [
                    'http_accept_language' => 'en-gb,fr;q=0.8, en;q=0.7',
                    'separator' => '_',
                ],
            ],
            'returns expected with the hyphen separator' => [
                'en-GB',
                [
                    'http_accept_language' => 'en-gb,fr;q=0.8, en;q=0.7',
                    'separator' => '-',
                ],
            ],
            'returns expected with the two-letter only on' => [
                'en',
                [
                    'http_accept_language' => 'ast,en;q=0.8,de;q=0.7,*;q=0.5',
                    'two_letter_only' => true,
                ],
            ],
            'returns expected with the two-letter only off' => [
                'ast',
                [
                    'http_accept_language' => 'ast,en;q=0.8,de;q=0.7,*;q=0.5',
                    'two_letter_only' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideHeaderValueWithDifferentLanguageLetterLength
     */
    public function testGetPreferredLanguageReturnsExpectedWhenTwoLetterOnlySet($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideHeaderValueWithDifferentLanguageLetterLength()
    {
        return [
            'two-letter primary language tag results primary subtag' => [
                'de',
                ['http_accept_language' => 'de']
            ],
            'two-letter primary language tag with extlang results primary subtag' => [
                'de',
                ['http_accept_language' => 'de-ger']
            ],
            'two-letter primary language tag with script results primary subtag with script' => [
                'de_Latn',
                ['http_accept_language' => 'de-Latn']
            ],
            'two-letter primary language tag with region results primary subtag with region' => [
                'de_DE',
                ['http_accept_language' => 'de-DE']
            ],
            'two-letter primary language tag with extlang, script, and region results primary subtag with script and region' => [
                'de_Latn_DE',
                ['http_accept_language' => 'de-get-Latn-DE']
            ],
            'three-letter primary language tag without two_letter_only option results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn'],
            ],
            'three-letter primary language tag without two_letter_only option with extlang results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn-ase'],
            ],
            'three-letter primary language tag without two_letter_only option with script results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn-Latn'],
            ],
            'three-letter primary language tag without two_letter_only option with region results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn-US'],
            ],
            'three-letter primary language tag without two_letter_only option with extlang, script, and region results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'sgn-ase-Latn-US'],
            ],
            'three-letter primary language tag with two_letter_only option results primary subtag' => [
                'sgn',
                [
                    'http_accept_language' => 'sgn',
                    'two_letter_only' => false,
                ],
            ],
            'three-letter primary language tag with two_letter_only option with extlang results primary subtag' => [
                'sgn',
                [
                    'http_accept_language' => 'sgn-ase',
                    'two_letter_only' => false,
                ],
            ],
            'three-letter primary language tag with two_letter_only option with script results primary subtag with script' => [
                'sgn_Latn',
                [
                    'http_accept_language' => 'sgn-Latn',
                    'two_letter_only' => false,
                ],
            ],
            'three-letter primary language tag with two_letter_only option with region results primary subtag with region' => [
                'sgn_US',
                [
                    'http_accept_language' => 'sgn-US',
                    'two_letter_only' => false,
                ],
            ],
            'three-letter primary language tag with two_letter_only option with extlang, script, and region results primary subtag with script and region' => [
                'sgn_Latn_US',
                [
                    'http_accept_language' => 'sgn-ase-Latn-US',
                    'two_letter_only' => false,
                ],
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
                [
                    'http_accept_language' => 'zH-HanT-Hk, en;q=0.9, *;q=0.5',
                    'accepted_languages' => ['zh-Hant-HK'],
                ],
            ],
            'language with hyphen intersects with underscored accepted_languages once results language' => [
                'zh_Hant_HK',
                [
                    'http_accept_language' => 'zH-HanT-Hk, en;q=0.9, *;q=0.5',
                    'accepted_languages' => ['zh_Hant_HK'],
                ],
            ],
            'language with underscore intersects with hyphenated accepted_languages once results language' => [
                'zh_Hant_HK',
                [
                    'http_accept_language' => 'zH_HanT_Hk, en;q=0.9, *;q=0.5',
                    'accepted_languages' => ['zh-Hant-HK'],
                ],
            ],
            'language with underscore intersects with underscored accepted_languages once results language' => [
                'zh_Hant_HK',
                [
                    'http_accept_language' => 'zH_HanT_Hk, en;q=0.9, *;q=0.5',
                    'accepted_languages' => ['zh_Hant_HK'],
                ],
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

    public function testGetPreferredLanguageReturnsExpectedWhenNoQualitiesProvided()
    {
        $options = [
            'http_accept_language' => 'fr;q=,en;q=,gr',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('fr', $service->getPreferredLanguage());
    }

    /**
     * Caught bugs.
     */
    public function testGetPreferredLanguageBugInRetrieveIntersectionWithAcceptableLanguages()
    {
        /**
         * Bug found: 14.02.2021
         * Details: The returned language doesn't follow the order from an HTTP Accept-Language header value.
         * The bug is in the retrieveAcceptableLanguagesIntersection() method and is related to a wrong order
         * of array_intersect_key() parameters.
         */
        $options = [
            'http_accept_language' => 'fr-CH,fr;q=0.8,en-US;q=0.5,en;q=0.3',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('fr', $service->getPreferredLanguage());
    }

    public function testGetPreferredLanguageBugInParseHeaderValue()
    {
        /**
         * Bug found: 13.01.2022
         * Details: The package crashes with a message array_combine(): Both parameters should have an equal number of elements.
         * The bug happens in the parseHeaderValue() method due to the specific HTTP Accept-Language header which is sent
         * by PetalBot browser running on Android OS.
         */
        $options = [
            'http_accept_language' => ';q=;q=0.3',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
    }
}
