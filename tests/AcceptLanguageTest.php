<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    public function testSetsDefaultLanguageWhenLanguageInformationIsEmptyAndHTTPAcceptLanguageIsNotAccessible()
    {
        $service = new AcceptLanguage();

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    public function testSetsDefaultLanguageFromOptionsWhenLanguageInformationIsEmptyAndHTTPAcceptLanguageIsNotAccessible()
    {
        $options = ['default_language' => 'de'];
        $service = new AcceptLanguage($options);

        $this->assertSame($options['default_language'], $service->getLanguage());
    }

    public function testSetsDefaultLanguageWhenRetrievedLanguageIsNotAccepted()
    {
        $options = ['http_accept_language' => 'pp', 'accepted_languages' => ['en', 'de', 'fr']];
        $service = new AcceptLanguage($options);

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    public function testSetsDefatulLanguageWhenAcceptedLanguagesContainsGarbage()
    {
        $options = ['http_accept_language' => 'pp', 'accepted_languages' => 'wrong_type'];
        $service = new AcceptLanguage($options);

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    public function testSetsTheHighestLanguageFromAcceptedLanguages()
    {
        $options = [
            'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
            'accepted_languages' => ['de'],
        ];
        $service = new AcceptLanguage($options);

        $this->assertSame('de', $service->getLanguage());
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
            'any language return default' => [
                AcceptLanguage::DEFAULT_LANGUAGE,
                ['http_accept_language' => '*'],
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
}
