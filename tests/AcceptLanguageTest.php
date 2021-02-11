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
                ['accept_language' => '*'],
            ],
            'two letters primary langtag' => [
                'en',
                ['accept_language' => 'en'],
            ],
            'three letters primary langtag' => [
                'sgn',
                ['accept_language' => 'sgn'],
            ],
            'sequence of primary tags' => [
                'de',
                ['accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
            ],
        ];
    }
}
