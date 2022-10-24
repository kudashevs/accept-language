<?php

namespace Kudashevs\AcceptLanguage\Tests\ValueObjects;

use Kudashevs\AcceptLanguage\ValueObjects\LanguageTag;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $instance = new LanguageTag('en', 1);

        $this->assertNotEmpty($instance->getLanguageTag());
        $this->assertNotEmpty($instance->getQuality());
    }
}
