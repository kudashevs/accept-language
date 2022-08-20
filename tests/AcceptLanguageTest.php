<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    const DEFAULT_LANGUAGE = 'en';

    /**
     * @test
     * @dataProvider provideDifferentWrongOptions
     */
    public function it_can_throw_exception_when_an_option_of_a_wrong_type(array $option)
    {
        $this->expectException(InvalidOptionArgumentException::class);
        $this->expectExceptionMessage('The option "' . key($option) . '" has a wrong value type');

        new AcceptLanguage($option);
    }

    public function provideDifferentWrongOptions()
    {
        return [
            'an http_accept_language option with a wrong value' => [
                ['http_accept_language' => null],
            ],
            'an default_language option with a wrong value' => [
                ['default_language' => null],
            ],
            'an accepted_languages option with a wrong value' => [
                ['accepted_languages' => null],
            ],
            'an two_letter_only option with a wrong value' => [
                ['two_letter_only' => null],
            ],
            'an separator option with a wrong value' => [
                ['separator' => null],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_retrieve_a_language()
    {
        $service = new AcceptLanguage();

        $this->assertNotEmpty($service->getPreferredLanguage());
        $this->assertNotEmpty($service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_default_language_when_no_options_and_no_header_are_provided()
    {
        $service = new AcceptLanguage();

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_preferred_language_from_options()
    {
        $options = ['default_language' => 'de'];
        $service = new AcceptLanguage($options);

        $this->assertSame($options['default_language'], $service->getPreferredLanguage());
        $this->assertSame($options['default_language'], $service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_default_language_when_a_language_is_not_listed_in_accepted_languages() {
        $options = [
            'http_accept_language' => 'pp',
            'accepted_languages' => ['en', 'de', 'fr'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_preferred_language_when_a_language_is_listed_in_accepted_languages() {
        $options = [
            'default_language' => 'es',
            'accepted_languages' => ['en', 'de', 'es'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame($options['default_language'], $service->getPreferredLanguage());
        $this->assertSame($options['default_language'], $service->getLanguage());
    }

    /**
     * @test
     * @dataProvider provideDifferentRequestHeaderValues
     */
    public function it_can_retrieve_the_expected_language(array $options, string $expected)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentRequestHeaderValues()
    {
        return [
            'any language tag results default' => [
                ['http_accept_language' => '*'],
                self::DEFAULT_LANGUAGE,
            ],
            'any language tag with highest quality results default' => [
                ['http_accept_language' => '*,de;q=0.7'],
                self::DEFAULT_LANGUAGE,
            ],
            'any language tag and language tag with equal quality results default' => [
                ['http_accept_language' => '*,es,de;q=0.7'],
                self::DEFAULT_LANGUAGE,
            ],
            'any language tag and language tag with equal quality results language' => [
                ['http_accept_language' => 'es,*,de;q=0.7'],
                'es',
            ],
            'two-letter primary language tag results language' => [
                ['http_accept_language' => 'fr'],
                'fr',
            ],
            'three-letter primary language tag results default' => [
                ['http_accept_language' => 'sgn'],
                self::DEFAULT_LANGUAGE,
            ],
            'three-letter primary language tag with optionresults default' => [
                [
                    'http_accept_language' => 'sgn',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'four letters primary language tag results default' => [
                ['http_accept_language' => 'test'],
                self::DEFAULT_LANGUAGE,
            ],
            'two-letter primary language tag with region results language' => [
                ['http_accept_language' => 'en-us'],
                'en_US',
            ],
            'two-letter primary language tag with script and region results language' => [
                ['http_accept_language' => 'zh-Hant-HK'],
                'zh_Hant_HK',
            ],
            'two-letter with 0 quality language tag results default' => [
                ['http_accept_language' => 'de;q=0'],
                self::DEFAULT_LANGUAGE,
            ],
            'three-letter with 0 quality language tag results language' => [
                [
                    'http_accept_language' => 'sgn;q=0',
                    'two_letter_only' => false,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'two-letter with 0.001 quality language tag results default' => [
                ['http_accept_language' => 'de;q=0.001'],
                'de',
            ],
            'three-letter with 0.001 quality language tag results language' => [
                [
                    'http_accept_language' => 'sgn;q=0.001',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'two-letter with quality language tag results language' => [
                ['http_accept_language' => 'de;q=0.5'],
                'de',
            ],
            'three-letter with quality language tag results language' => [
                [
                    'http_accept_language' => 'sgn;q=0.5',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'two-letter with 0.999 quality language tag results default' => [
                ['http_accept_language' => 'de;q=0.999'],
                'de',
            ],
            'three-letter with 0.999 quality language tag results language' => [
                [
                    'http_accept_language' => 'sgn;q=0.999',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'two-letter with 1 quality language tag results default' => [
                ['http_accept_language' => 'de;q=1'],
                'de',
            ],
            'three-letter with 1 quality language tag results language' => [
                [
                    'http_accept_language' => 'sgn;q=1',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'two-letter with 1.001 quality language tag results default' => [
                ['http_accept_language' => 'de;q=1.001'],
                self::DEFAULT_LANGUAGE,
            ],
            'three-letter with 1.001 quality language tag results language' => [
                [
                    'http_accept_language' => 'sgn;q=1.001',
                    'two_letter_only' => false,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'four letters with quality language tag results default' => [
                ['http_accept_language' => 'test;q=0.5'],
                self::DEFAULT_LANGUAGE,
            ],
            'sequence of primary tags results language' => [
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
                'de',
            ],
            'example all lowercase results language' => [
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
                'de',
            ],
            'example part uppercase results language' => [
                ['http_accept_language' => 'de-DE,de;q=0.9,en;q=0.8'],
                'de_DE',
            ],
            'mozilla Accept-Language page examples basic results language' => [
                ['http_accept_language' => 'de'],
                'de',
            ],
            'mozilla Accept-Language page examples hyphenated results language' => [
                ['http_accept_language' => 'de-CH'],
                'de_CH',
            ],
            'mozilla Accept-Language page examples complex results language' => [
                ['http_accept_language' => 'en-US,en;q=0.5'],
                'en_US',
            ],
            'mozilla Accept-Language page examples complex with space results language' => [
                ['http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'],
                'fr_CH',
            ],
            'RFC 2616 14.4 Accept-Language example results language' => [
                ['http_accept_language' => 'da, en-gb;q=0.8, en;q=0.7'],
                'da',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentAcceptedLanguagesValues
     */
    public function it_can_retrieve_the_preferred_language_when_the_accepted_languages_are_set($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentAcceptedLanguagesValues()
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
     * @dataProvider provideDifferentOptions
     */
    public function testGetPreferredLanguageReturnsExpectedWhenSpecificOptionIsSet($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentOptions()
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
     * @dataProvider provideDifferentRequestHeadersWithDifferentLanguageLetterLength
     */
    public function testGetPreferredLanguageReturnsExpectedWhenTwoLetterOnlySet($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentRequestHeadersWithDifferentLanguageLetterLength()
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
     * @dataProvider provideDifferentAcceptedLanguagesValuesRelatedToNormalization
     */
    public function testGetPreferredLanguageReturnsNormalized($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentAcceptedLanguagesValuesRelatedToNormalization()
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
     * @dataProvider provideDifferentRequestHeadersWithMalformedValues
     */
    public function testGetPreferredLanguageProcessesIncorrectOrWronglyFormedHeaderValue($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentRequestHeadersWithMalformedValues()
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
