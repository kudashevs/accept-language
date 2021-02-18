<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    /**
     * @dataProvider provideWrongOptions
     */
    public function testSetOptionsThrowsExceptionOnWrongOptionParameterType($option)
    {
        $this->expectException(InvalidOptionArgumentException::class);
        $this->expectExceptionMessageRegExp('/The option ' . key($option) . ' has a wrong value type .+/');

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

    public function testGetLanguageReturnsDefaultLanguageWhenLanguageInformationIsEmptyAndHTTPAcceptLanguageIsNotAccessible()
    {
        $service = new AcceptLanguage();

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    public function testGetLanguageReturnsDefaultLanguageFromOptionsWhenLanguageInformationIsEmptyAndHTTPAcceptLanguageIsNotAccessible()
    {
        $options = ['default_language' => 'de'];
        $service = new AcceptLanguage($options);

        $this->assertSame($options['default_language'], $service->getLanguage());
    }

    public function testGetLanguageReturnsDefaultLanguageWhenRetrievedLanguageIsNotAccepted()
    {
        $options = [
            'http_accept_language' => 'pp',
            'accepted_languages' => ['en', 'de', 'fr']
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    public function testGetLanguageReturnsExpectedLanguageWhenLanguagesIntersectOnce()
    {
        $options = [
            'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
            'accepted_languages' => ['de'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('de', $service->getLanguage());
    }

    public function testGetLanguageReturnsLanguageWithTheHighestQualityWhenLanguagesIntersectWithQuality1()
    {
        $options = [
            'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
            'accepted_languages' => ['de', 'fr'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('fr', $service->getLanguage());
    }

    public function testGetLanguageReturnsLanguageWithTheHighestQualityWhenLanguagesIntersectWithQualityBelow1()
    {
        $options = [
            'http_accept_language' => 'de;q=0.7,fr;q=0.333,es;q=0.333',
            'accepted_languages' => ['en', 'es'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('es', $service->getLanguage());
    }

    public function testGetLanguageReturnsOptionalDefaultLanguageWhenAcceptedLanguagesContainsOptionalLanguage()
    {
        $options = [
            'default_language' => 'es',
            'accepted_languages' => ['en', 'de', 'es'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame($options['default_language'], $service->getLanguage());
    }

    public function testGetLanguageReturnsDefaultLanguageWhenOptionalDefaultLanguageIsNotAccepted()
    {
        $options = [
            'default_language' => 'pp',
            'accepted_languages' => ['en', 'de'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    /**
     * @dataProvider provideLanguageInformation
     */
    public function testGetLanguageReturnsExpected($expected, $options)
    {
        $service = new AcceptLanguage($options);
        $result = $service->getLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideLanguageInformation()
    {
        return [
            'any language tag results default' => [
                AcceptLanguage::DEFAULT_LANGUAGE,
                ['http_accept_language' => '*'],
            ],
            'any language tag with highest quality results default' => [
                AcceptLanguage::DEFAULT_LANGUAGE,
                ['http_accept_language' => '*,de;q=0.7'],
            ],
            'any language tag and lang tag with equal quality results language' => [
                'es',
                ['http_accept_language' => '*,es,de;q=0.7'],
            ],
            'two letters primary langtag' => [
                'en',
                ['http_accept_language' => 'en'],
            ],
            'three letters primary langtag' => [
                'sgn',
                ['http_accept_language' => 'sgn'],
            ],
            'sequence of primary tags' => [
                'de',
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
            ],
            'example all lowercase de,en-us;q=0.7...' => [
                'de', ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3']
            ],
            'example part uppercase de-DE,de;q=0.9...' => [
                'de', ['http_accept_language' => 'de-DE,de;q=0.9,en;q=0.8']
            ],
            'mozilla Accept-Language page examples basic' => [
                'de', ['http_accept_language' => 'de']
            ],
            'mozilla Accept-Language page examples hyphenated' => [
                'de', ['http_accept_language' => 'de-CH']
            ],
            'mozilla Accept-Language page examples complex' => [
                'de', ['http_accept_language' => 'de-CH']
            ],
            'mozilla Accept-Language page examples complex with space' => [
                'fr', ['http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5']
            ],
            'RFC 2616 14.4 Accept-Language example' => [
                'da', ['http_accept_language' => 'da, en-gb;q=0.8, en;q=0.7']
            ]
        ];
    }
    
    /**
     * Caught bugs.
     */
    public function testGetLanguageCaughtTheIntersectionBugInRetrieveAcceptableLanguagesIntersection()
    {
        $options = [
            'http_accept_language' => 'fr-CH,fr;q=0.8,en-US;q=0.5,en;q=0.3',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('fr', $service->getLanguage());
    }
}
