<?php

namespace Kudashevs\AcceptLanguage\Tests\Acceptance;

use Illuminate\Log\Logger;
use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionValue;
use Kudashevs\AcceptLanguage\Tests\ExtendedTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class AcceptLanguageTest extends ExtendedTestCase
{
    #[Test]
    public function it_throws_an_exception_when_a_wrong_option_is_provided(): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('wrong value');

        new AcceptLanguage(['separator' => 42]);
    }

    #[Test]
    public function it_throws_an_exception_when_a_wrong_default_language_is_provided(): void
    {
        $this->expectException(InvalidOptionValue::class);
        $this->expectExceptionMessage('verywrong_language');

        new AcceptLanguage(['default_language' => 'verywrong_language']);
    }

    #[Test]
    public function it_throws_an_exception_when_a_wrong_log_level_is_provided(): void
    {
        $this->expectException(InvalidLogLevelName::class);
        $this->expectExceptionMessage('wrong');

        new AcceptLanguage([
            'log_level' => 'wrong',
        ]);
    }

    #[Test]
    public function it_throws_an_exception_when_a_wrong_log_event_name_is_provided(): void
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessage('wrong');

        new AcceptLanguage([
            'log_only' => ['wrong'],
        ]);
    }

    /**
     * @see /README.md The explanations are in the Usage section see a Note.
     */
    #[Test]
    public function it_returns_empty_result_when_it_is_not_processed(): void
    {
        $service = new AcceptLanguage();

        $language = $service->getLanguage();

        $this->assertEmpty($language);
    }

    #[Test]
    public function it_can_retrieve_a_default_language(): void
    {
        $service = new AcceptLanguage();
        $service->process();

        $language = $service->getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('en', $language);
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_a_non_exact_match_language(): void
    {
        $header = 'de, fr;q=0.9, fr-CH;q=0.8, en;q=0.7, *;q=0.5';

        $service = new AcceptLanguage([
            'http_accept_language' => $header,
            'accepted_languages' => ['fr', 'en'],
        ]);
        $service->process();

        $this->assertSame('fr', $service->getLanguage());
        $this->assertSame(0.9, $service->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_a_non_exact_match_language_and_a_subtag(): void
    {
        $header = 'de, fr;q=0.9, fr-CH;q=0.8, en;q=0.7, *;q=0.5';

        $service = new AcceptLanguage([
            'http_accept_language' => $header,
            'accepted_languages' => ['fr-CH', 'en'],
        ]);
        $service->process();

        $this->assertSame('fr_CH', $service->getLanguage());
        $this->assertSame(0.8, $service->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_a_non_exact_match_derivative(): void
    {
        $header = 'de, fr-CH;q=0.9, fr;q=0.8, en;q=0.7, *;q=0.5';

        $service = new AcceptLanguage([
            'http_accept_language' => $header,
            'accepted_languages' => ['fr', 'en'],
        ]);
        $service->process();

        $this->assertSame('fr', $service->getLanguage());
        $this->assertSame(0.9, $service->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_an_exact_match_language(): void
    {
        $header = 'de, fr-CH;q=0.9, fr;q=0.8, en;q=0.7, *;q=0.5';

        $service = new AcceptLanguage([
            'http_accept_language' => $header,
            'accepted_languages' => ['fr', 'en'],
            'exact_match_only' => true,
        ]);
        $service->process();

        $this->assertSame('fr', $service->getLanguage());
        $this->assertSame(0.8, $service->getQuality());
    }

    /**
     * @see /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    public function it_can_retrieve_an_exact_match_language_and_a_subtag(): void
    {
        $header = 'de, fr-CH;q=0.9, fr;q=0.8, en;q=0.7, *;q=0.5';

        $service = new AcceptLanguage([
            'http_accept_language' => $header,
            'accepted_languages' => ['fr-CH', 'en'],
            'exact_match_only' => true,
        ]);
        $service->process();

        $this->assertSame('fr_CH', $service->getLanguage());
        $this->assertSame(0.9, $service->getQuality());
    }

    /**
     * @see          /README.md The explanations are in the Options section see Notes.
     */
    #[Test]
    #[DataProvider('provideExactMatchCasesFromReadme')]
    public function it_can_retrieve_a_language_with_exact_match_options(
        array $options,
        string $expectedLanguage,
        int|float $expectedQuality,
    ): void {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expectedLanguage, $service->getLanguage());
        $this->assertSame($expectedQuality, $service->getQuality());
    }

    public static function provideExactMatchCasesFromReadme(): array
    {
        return [
            'the header starts with fr-CH, and exact_match_only set to false, and and accept_languages set to fr (derivative)' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, *;q=0.5',
                    'exact_match_only' => false,
                    'accepted_languages' => ['fr'],
                ],
                'fr',
                1,
            ],
            'the header starts with fr-CH, and exact_match_only set to false, and and accept_languages set to fr-ch' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, *;q=0.5',
                    'exact_match_only' => false,
                    'accepted_languages' => ['fr-ch'],
                ],
                'fr_CH',
                1,
            ],
            'the header starts with fr-CH, and exact_match_only set to true, and and accept_languages set to fr' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, *;q=0.5',
                    'exact_match_only' => true,
                    'accepted_languages' => ['fr'],
                ],
                'fr',
                0.9,
            ],
            'the header starts with fr-CH, and exact_match_only set to true, and and accept_languages set to fr-ch' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, *;q=0.5',
                    'exact_match_only' => true,
                    'accepted_languages' => ['fr-ch'],
                ],
                'fr_CH',
                1,
            ],
            'the header starts with fr, and exact_match_only set to false, and and accept_languages set to fr' => [
                [
                    'http_accept_language' => 'fr, fr-CH;q=0.9, *;q=0.5',
                    'exact_match_only' => false,
                    'accepted_languages' => ['fr'],
                ],
                'fr',
                1,
            ],
            'the header starts with fr, and exact_match_only set to false, and and accept_languages set to fr-ch' => [
                [
                    'http_accept_language' => 'fr, fr-CH;q=0.9, *;q=0.5',
                    'exact_match_only' => false,
                    'accepted_languages' => ['fr-ch'],
                ],
                'fr_CH',
                0.9,
            ],
            'the header starts with fr, and exact_match_only set to true, and and accept_languages set to fr' => [
                [
                    'http_accept_language' => 'fr, fr-CH;q=0.9, *;q=0.5',
                    'exact_match_only' => true,
                    'accepted_languages' => ['fr'],
                ],
                'fr',
                1,
            ],
            'the header starts with fr, and exact_match_only set to true, and and accept_languages set to fr-ch' => [
                [
                    'http_accept_language' => 'fr, fr-CH;q=0.9, *;q=0.5',
                    'exact_match_only' => true,
                    'accepted_languages' => ['fr-ch'],
                ],
                'fr_CH',
                0.9,
            ],

        ];
    }

    #[Test]
    public function it_can_apply_the_separator_related_options(): void
    {
        $service = new AcceptLanguage([
            'default_language' => 'fr_CH',
            'separator' => '-',
        ]);
        $service->process();

        $language = $service->getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr-CH', $language);
    }

    #[Test]
    public function it_can_apply_the_subtag_related_options(): void
    {
        $service = new AcceptLanguage([
            'default_language' => 'fr-Latn-CH',
            'use_script_subtag' => false,
            'use_region_subtag' => false,
        ]);
        $service->process();

        $language = $service->getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr', $language);
    }

    #[Test]
    public function it_can_log_gathered_information_by_default(): void
    {
        $mock = $this->createMock(Logger::class);
        $mock->expects($this->atLeastOnce())
            ->method('debug');

        $service = new AcceptLanguage([
            'log_level' => 'debug',
        ]);
        $service->useLogger($mock);
        $service->process();
    }
}
