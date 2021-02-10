<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    public function testCreatesExpectedInstance()
    {
        $acceptLanguage = new AcceptLanguage();

        $this->assertInstanceOf(AcceptLanguage::class, $acceptLanguage);
    }
}
