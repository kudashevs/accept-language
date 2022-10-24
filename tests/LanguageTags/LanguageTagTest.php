<?php

namespace Kudashevs\AcceptLanguage\Tests\LanguageTags;

use Kudashevs\AcceptLanguage\LanguageTags\LanguageTag;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $instance = new LanguageTag('en', 1);

        $this->assertNotEmpty($instance->getTag());
        $this->assertNotEmpty($instance->getQuality());
    }
}
