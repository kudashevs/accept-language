<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    public function testSetRawSetsDefaultLanguageWhenLanguageInformationIsEmptyAndHTTPAcceptLanguageIsNotAccessible()
    {
        $service = new AcceptLanguage();

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    public function testGetLanguageReturnsDefaultIfAnyLanguageProvided()
    {
        $service = new AcceptLanguage();
        $service->process('*');

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
