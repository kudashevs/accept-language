<?php

namespace Kudashevs\AcceptLanguage\Tests\Acceptance;

use Illuminate\Log\Logger;
use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionValue;
use Kudashevs\AcceptLanguage\Facades\AcceptLanguage as AcceptLanguageFacade;
use Kudashevs\AcceptLanguage\Tests\ExtendedTestCase;

class AcceptLanguageTest extends ExtendedTestCase
{
    /** @test */
    public function it_throws_an_exception_when_a_wrong_option_is_provided(): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('wrong value');

        new AcceptLanguage(['separator' => 42]);
    }

    /** @test */
    public function it_throws_an_exception_when_a_wrong_default_language_is_provided(): void
    {
        $this->expectException(InvalidOptionValue::class);
        $this->expectExceptionMessage('verywrong_language');

        new AcceptLanguage(['default_language' => 'verywrong_language']);
    }

    /** @test */
    public function it_throws_an_exception_when_a_wrong_log_level_is_provided(): void
    {
        $this->expectException(InvalidLogLevelName::class);
        $this->expectExceptionMessage('wrong');

        new AcceptLanguage([
            'log_level' => 'wrong',
        ]);
    }

    /** @test */
    public function it_throws_an_exception_when_a_wrong_log_event_name_is_provided(): void
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessage('wrong');

        new AcceptLanguage([
            'log_only' => ['wrong'],
        ]);
    }

    /** @test */
    public function it_can_retrieve_a_language(): void
    {
        $service = new AcceptLanguage();
        $service->process();

        $language = $service->getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('en', $language);
    }

    /** @test */
    public function it_can_retrieve_a_language_through_a_facade(): void
    {
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertStringContainsString('en', $language);
    }

    /** @test */
    public function it_can_apply_some_separator_related_options(): void
    {
        app('config')->set('accept-language.default_language', 'fr_CH');
        app('config')->set('accept-language.separator', '-');
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr-CH', $language);
    }

    /** @test */
    public function it_can_apply_some_subtags_related_options(): void
    {
        app('config')->set('accept-language.default_language', 'fr-Latn-CH');
        app('config')->set('accept-language.use_script_subtag', false);
        app('config')->set('accept-language.use_region_subtag', false);
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr', $language);
    }

    /** @test */
    public function it_can_apply_some_log_related_options(): void
    {
        $this->partialMock(Logger::class, function ($mock) {
            $mock->shouldReceive('debug')->atLeast(1);
        });

        app('config')->set('accept-language.log_activity', true);
        app('config')->set('accept-language.log_level', 'debug');
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('en', $language);
    }
}
