<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\Loggers\DummyLogger;
use Kudashevs\AcceptLanguage\LogProviders\LogProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogProviderTest extends TestCase
{
    #[Test]
    public function it_can_throw_an_exception_when_an_option_of_the_wrong_type(): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('The option "log_only" has a wrong value type');

        new LogProvider(new DummyLogger(), [
            'log_only' => 42,
        ]);
    }

    #[Test]
    public function it_can_throw_an_exception_when_a_wrong_log_only_event(): void
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessageMatches('/wrong.+is not/');

        new LogProvider(new DummyLogger(), [
            'log_only' => ['wrong'],
        ]);
    }

    #[Test]
    public function it_can_throw_an_exception_when_wrong_log_only_events(): void
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessageMatches('/mistaken.+incorrect.+are not/');

        new LogProvider(new DummyLogger(), [
            'log_only' => [
                'mistaken',
                'incorrect',
            ],
        ]);
    }

    /**
     * @see LogProviderTest::it_can_handle_a_valid_log_level_option()
     */
    #[Test]
    public function it_can_throw_an_exception_when_a_wrong_log_level(): void
    {
        $this->expectException(InvalidLogLevelName::class);
        $this->expectExceptionMessage('wrong');

        new LogProvider(new DummyLogger(), [
            'log_level' => 'wrong',
        ]);
    }

    #[Test]
    public function it_can_throw_an_exception_when_a_wrong_event_name(): void
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessage('wrong');

        $provider = new LogProvider(new DummyLogger());
        $provider->log('wrong', '');
    }

    /**
     * @see LogProviderTest::it_can_throw_an_exception_when_a_wrong_log_level()
     */
    #[Test]
    public function it_can_handle_a_valid_log_level_option(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('emergency')
            ->with(
                $this->stringContains('retrieve_header')
            );

        $provider = new LogProvider($loggerMock, [
            'log_level' => 'Emergency',
        ]);

        $provider->log('retrieve_header', 'anything');
    }

    #[Test]
    public function it_can_skip_handling_an_event_not_listed_in_log_only(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())
            ->method('info');

        $provider = new LogProvider($loggerMock, [
            'log_only' => ['retrieve_preferred_language'],
        ]);

        $provider->log('retrieve_header', 'anything');
        $provider->log('retrieve_raw_languages', 'anything');
    }

    #[Test]
    public function is_can_handle_only_an_event_listed_in_log_only(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('retrieve_header')
            );

        $provider = new LogProvider($loggerMock, [
            'log_only' => ['retrieve_header'],
        ]);

        $provider->log('retrieve_header', 'fr-CH');
        $provider->log('retrieve_raw_languages', ['anything']);
        $provider->log('retrieve_preferred_language', 'en');
    }

    #[Test]
    #[DataProvider('provideDifferentEvents')]
    public function it_can_handle_an_event(string $event, $data, string $expected): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with($this->matchesRegularExpression($expected));

        $provider = new LogProvider($loggerMock);
        $provider->log($event, $data);
    }

    public static function provideDifferentEvents(): array
    {
        return [
            'retrieve_header log event' => [
                'retrieve_header',
                'fr-CH,fr;q=0.9,en;q=0.8,de;q=0.7,*;q=0.5',
                '/fr-CH,fr;q=0.9/',
            ],
            'retrieve_raw_languages log event' => [
                'retrieve_raw_languages',
                [
                    DefaultLanguage::create('fr-CH', 1.0),
                    DefaultLanguage::create('verywrong', 0),
                ],
                '/verywrong.*invalid/',
            ],
            'retrieve_normalized_languages log event' => [
                'retrieve_normalized_languages',
                [
                    DefaultLanguage::create('fr-CH', 1.0),
                    DefaultLanguage::create('fr', 0.9),
                    DefaultLanguage::create('en', 0.8),
                ],
                '/fr-CH.*0.8/',
            ],
            'retrieve_accepted_languages log event' => [
                'retrieve_accepted_languages',
                [
                    DefaultLanguage::create('fr-CH', null),
                    DefaultLanguage::create('fr', null),
                    DefaultLanguage::create('en', null),
                ],
                '/fr-CH,fr/',
            ],
            'retrieve_preferred_languages log event' => [
                'retrieve_preferred_languages',
                [
                    DefaultLanguage::create('fr-CH', 1.0),
                    DefaultLanguage::create('fr', 0.9),
                    DefaultLanguage::create('en', 0.8),
                ],
                '/fr-CH.*0.8/',
            ],
            'retrieve_preferred_language log event' => [
                'retrieve_preferred_language',
                'fr-CH',
                '/fr-CH/',
            ],
        ];
    }
}
