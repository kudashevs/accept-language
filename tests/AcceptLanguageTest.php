<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
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
        $options = ['http_accept_language' => 'pp', 'accepted_languages' => ['en', 'de', 'fr']];
        $service = new AcceptLanguage($options);

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    public function testGetLanguageReturnsDefatulLanguageWhenAcceptedLanguagesContainsGarbage()
    {
        $options = ['http_accept_language' => 'pp', 'accepted_languages' => 'wrong_type'];
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
            'mozilla example with space fr-CH, fr;q=0.9, en;q=0.8...' => [
                'fr', ['http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5']
            ],
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
