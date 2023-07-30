<?php

namespace Kudashevs\AcceptLanguage\Tests\Acceptance;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Facades\AcceptLanguage as AcceptLanguageFacade;
use Kudashevs\AcceptLanguage\Tests\ExtendedTestCase;

class AcceptLanguageTest extends ExtendedTestCase
{
    /** @test */
    public function an_instance_can_throw_invalid_option_argument_when_a_wrong_option_provided()
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('wrong value');

        new AcceptLanguage(['separator' => 42]);
    }

    /** @test */
    public function an_instance_can_retrieve_a_language()
    {
        $service = new AcceptLanguage();
        $service->process();

        $result = $service->getLanguage();

        $this->assertNotEmpty($result);
        $this->assertSame('en', $result);
    }

    /** @test */
    public function a_facade_can_retrieve_a_language()
    {
        $result = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('en', $result);
    }
}
