<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    public function testGetLanguageReturnsDefaultIfAnyLanguageProvided()
    {
        $service = new AcceptLanguage('*');

        $this->assertSame(AcceptLanguage::DEFAULT_LANGUAGE, $service->getLanguage());
    }
}
