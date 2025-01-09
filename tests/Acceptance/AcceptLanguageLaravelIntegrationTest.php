<?php

namespace Kudashevs\AcceptLanguage\Tests\Acceptance;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionValue;
use Kudashevs\AcceptLanguage\Facades\AcceptLanguage as AcceptLanguageFacade;
use Kudashevs\AcceptLanguage\Tests\ExtendedTestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;

class AcceptLanguageLaravelIntegrationTest extends ExtendedTestCase
{
    #[Test]
    public function it_throws_an_exception_when_a_wrong_option_is_provided(): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('wrong value');

        app('config')->set('accept-language.separator', 42);
        AcceptLanguageFacade::getLanguage();
    }

    #[Test]
    public function it_throws_an_exception_when_a_wrong_default_language_is_provided(): void
    {
        $this->expectException(InvalidOptionValue::class);
        $this->expectExceptionMessage('verywrong_language');

        app('config')->set('accept-language.default_language', 'verywrong_language');
        AcceptLanguageFacade::getLanguage();
    }

    #[Test]
    public function it_throws_an_exception_when_a_wrong_log_level_is_provided(): void
    {
        $this->expectException(InvalidLogLevelName::class);
        $this->expectExceptionMessage('wrong');

        app('config')->set('accept-language.log_level', 'wrong');
        AcceptLanguageFacade::getLanguage();
    }

    #[Test]
    public function it_throws_an_exception_when_a_wrong_log_event_name_is_provided(): void
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessage('wrong');

        app('config')->set('accept-language.log_only', ['wrong']);
        AcceptLanguageFacade::getLanguage();
    }

    /**
     * @see /README.md The explanations are in the Usage section see a Note.
     */
    #[Test]
    public function it_cannot_return_empty_result_because_it_is_processed_by_default(): void
    {
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('en', $language);
    }

    #[Test]
    public function it_can_retrieve_a_default_language(): void
    {
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('en', $language);
    }

    #[Test]
    public function it_can_retrieve_a_default_language_through_a_facade(): void
    {
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('en', $language);
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_a_non_exact_match_language(): void
    {
        app('config')->set('accept-language.http_accept_language', 'de, fr;q=0.9, fr-CH;q=0.8, en;q=0.7, *;q=0.5');
        app('config')->set('accept-language.accepted_languages', ['fr', 'en']);
        AcceptLanguageFacade::getLanguage();

        $this->assertSame('fr', app('acceptlanguage')->getLanguage());
        $this->assertSame(0.9, app('acceptlanguage')->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_a_non_exact_match_language_and_a_subtag(): void
    {
        $header = 'de, fr;q=0.9, fr-CH;q=0.8, en;q=0.7, *;q=0.5';

        app('config')->set('accept-language.http_accept_language', $header);
        app('config')->set('accept-language.accepted_languages', ['fr-CH', 'en']);
        AcceptLanguageFacade::getLanguage();

        $this->assertSame('fr_CH', app('acceptlanguage')->getLanguage());
        $this->assertSame(0.8, app('acceptlanguage')->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_a_non_exact_match_derivative(): void
    {
        $header = 'de, fr-CH;q=0.9, fr;q=0.8, en;q=0.7, *;q=0.5';

        app('config')->set('accept-language.http_accept_language', $header);
        app('config')->set('accept-language.accepted_languages', ['fr', 'en']);
        AcceptLanguageFacade::getLanguage();

        $this->assertSame('fr', app('acceptlanguage')->getLanguage());
        $this->assertSame(0.9, app('acceptlanguage')->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_an_exact_match_language(): void
    {
        $header = 'de, fr-CH;q=0.9, fr;q=0.8, en;q=0.7, *;q=0.5';

        app('config')->set('accept-language.http_accept_language', $header);
        app('config')->set('accept-language.accepted_languages', ['fr', 'en']);
        app('config')->set('accept-language.exact_match_only', true);
        AcceptLanguageFacade::getLanguage();

        $this->assertSame('fr', app('acceptlanguage')->getLanguage());
        $this->assertSame(0.8, app('acceptlanguage')->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_an_exact_match_language_and_a_subtag(): void
    {
        $header = 'de, fr-CH;q=0.9, fr;q=0.8, en;q=0.7, *;q=0.5';

        app('config')->set('accept-language.http_accept_language', $header);
        app('config')->set('accept-language.accepted_languages', ['fr-CH', 'en']);
        app('config')->set('accept-language.exact_match_only', true);
        AcceptLanguageFacade::getLanguage();

        $this->assertSame('fr_CH', app('acceptlanguage')->getLanguage());
        $this->assertSame(0.9, app('acceptlanguage')->getQuality());
    }

    #[Test]
    public function it_can_apply_the_separator_related_options(): void
    {
        app('config')->set('accept-language.default_language', 'fr_CH');
        app('config')->set('accept-language.separator', '-');
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr-CH', $language);
    }

    #[Test]
    public function it_can_apply_the_subtag_related_options(): void
    {
        app('config')->set('accept-language.default_language', 'fr-Latn-CH');
        app('config')->set('accept-language.use_script_subtag', false);
        app('config')->set('accept-language.use_region_subtag', false);
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr', $language);
    }

    #[Test]
    public function it_cannot_log_gathered_information_by_default(): void
    {
        $defaultLevel = app('config')->get('accept-language.log_level');

        $this->instance('log',
            $this->partialMock(LoggerInterface::class, function ($mock) use ($defaultLevel) {
                $mock->shouldReceive($defaultLevel)->never();
            })
        );

        AcceptLanguageFacade::getLanguage();
    }

    #[Test]
    public function it_can_log_gathered_information_when_the_option_is_provided(): void
    {
        $defaultLevel = app('config')->get('accept-language.log_level');

        $this->instance('log',
            $this->partialMock(LoggerInterface::class, function ($mock) use ($defaultLevel) {
                $mock->shouldReceive($defaultLevel)->atLeast()->times(1);
            })
        );

        app('config')->set('accept-language.log_activity', true);
        AcceptLanguageFacade::getLanguage();
    }
}
