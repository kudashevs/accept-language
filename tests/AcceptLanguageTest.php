<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    public function testGetRawReturnsExpectedRawAcceptLanguage()
    {
        $input = 'es,en-us,fr-ca';
        $expected = 'es,en-us,fr-ca';

        $service = new AcceptLanguage($input);

        $this->assertSame($expected, $service->getRaw());
    }

    public function testGetLanguageReturnsDefaultIfAnyLanguageProvided()
    {
        $service = new AcceptLanguage('*');

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }
}
