<?php

namespace Kudashevs\AcceptLanguage\Tests\Acceptance;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Facades\AcceptLanguage as AcceptLanguageFacade;
use Kudashevs\AcceptLanguage\Tests\ExtendedTestCase;

class AcceptLanguageTest extends ExtendedTestCase
{
    /** @test */
    public function an_instance_can_throw_an_invalid_option_type_exception_when_a_wrong_option_provided()
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('wrong value');

        new AcceptLanguage(['separator' => 42]);
    }

    /** @test */
    public function an_instance_can_throw_an_invalid_log_level_name_exception_when_a_wrong_log_level_provided()
    {
        $this->expectException(InvalidLogLevelName::class);
        $this->expectExceptionMessage('wrong');

        new AcceptLanguage([
            'log_level' => 'wrong',
        ]);
    }

    /** @test */
    public function an_instance_can_retrieve_a_language()
    {
        $service = new AcceptLanguage();
        $service->process();

        $language = $service->getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('en', $language);
    }

    /** @test */
    public function a_facade_can_retrieve_a_language()
    {
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertStringContainsString('en', $language);
    }

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('en', $result);
    }
}
