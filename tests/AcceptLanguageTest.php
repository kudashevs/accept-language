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
            'http_accept_language wrong not string value' => [
                [
                    'http_accept_language' => null,
                ],
            ],
            'default_language wrong not string value' => [
                [
                    'default_language' => null,
                ],
            ],
            'accepted_languages wrong not array value' => [
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

    public function testGetPreferredLanguageReturnsDefaultLanguageWhenRetrievedLanguageIsNotAccepted()
    {
        $options = [
            'http_accept_language' => 'pp',
            'accepted_languages' => ['en', 'de', 'fr']
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
    }

    public function testGetPreferredLanguageReturnsOptionalDefaultLanguageWhenAcceptedLanguagesContainsOptionalLanguage()
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
            'any language tag and lang tag with equal quality results language' => [
                'es',
                ['http_accept_language' => '*,es,de;q=0.7'],
            ],
            'two letters primary langtag results language' => [
                'en',
                ['http_accept_language' => 'en'],
            ],
            'three letters primary langtag results language' => [
                'sgn',
                ['http_accept_language' => 'sgn'],
            ],
            'four letters primary langtag results default' => [
                self::DEFAULT_LANGUAGE,
                ['http_accept_language' => 'test'],
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
                'de',
                ['http_accept_language' => 'de-DE,de;q=0.9,en;q=0.8'],
            ],
            'mozilla Accept-Language page examples basic results language' => [
                'de',
                ['http_accept_language' => 'de'],
            ],
            'mozilla Accept-Language page examples hyphenated results language' => [
                'de',
                ['http_accept_language' => 'de-CH'],
            ],
            'mozilla Accept-Language page examples complex results language' => [
                'en',
                ['http_accept_language' => 'en-US,en;q=0.5'],
            ],
            'mozilla Accept-Language page examples complex with space results language' => [
                'fr',
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
            'language in accepted intersects once results language' => [
                'de',
                [ 'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5', 'accepted_languages' => ['de'], ],
            ],
            'language in accepted intersects once results language when it is of quality 1' => [
                'fr',
                [ 'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5', 'accepted_languages' => ['de', 'fr'], ],
            ],
            'language in accepted intersects once results language when it is of quality below 1' => [
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
