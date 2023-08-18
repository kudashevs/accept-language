<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Language\Language;
use Kudashevs\AcceptLanguage\Loggers\DummyLogger;
use Kudashevs\AcceptLanguage\LogProviders\LogProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogProviderTest extends TestCase
{
    /** @test */
    public function it_can_throw_an_exception_when_an_option_of_a_wrong_type()
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('The option "log_only" has a wrong value type');

        new LogProvider(new DummyLogger(), [
            'log_only' => [],
        ]);
    }

    /** @test */
    public function it_can_throw_an_exception_when_a_wrong_event_name()
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessage('wrong');

        $provider = new LogProvider(new DummyLogger());
        $provider->log('wrong', '');
    }

    /** @test */
    public function it_can_skip_handling_an_event_not_listed_in_log_only()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())
            ->method('info');

        $provider = new LogProvider($loggerMock, [
            'log_only' => 'retrieve_preferred_language',
        ]);

        $provider->log('retrieve_header', 'anything');
        $provider->log('retrieve_raw_languages', 'anything');
    }

    /** @test */
    public function is_can_handle_only_an_event_listed_in_log_only()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('retrieve_header')
            );

        $provider = new LogProvider($loggerMock, [
            'log_only' => 'retrieve_header',
        ]);

        $provider->log('retrieve_header', 'fr-CH');
        $provider->log('retrieve_raw_languages', ['anything']);
        $provider->log('retrieve_preferred_language', 'en');
    }

    /**
     * @test
     * @dataProvider provideDifferentLogOnlySeparators
     */
    public function it_can_handle_only_events_listed_in_log_only(array $options)
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                [$this->stringContains('retrieve_header')],
                [$this->stringContains('retrieve_preferred_language')],
            );

        $provider = new LogProvider($loggerMock, $options);

        $provider->log('retrieve_header', 'fr-CH');
        $provider->log('retrieve_raw_languages', 'anything');
        $provider->log('retrieve_preferred_language', 'en');
    }

    public function provideDifferentLogOnlySeparators(): array
    {
        return [
            'a pipe separator' => [
                ['log_only' => 'retrieve_header|retrieve_preferred_language'],
            ],
            'a comma separator' => [
                ['log_only' => 'retrieve_header,retrieve_preferred_language'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentEvents
     */
    public function it_can_handle_an_event(string $event, $data, string $expected): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with($this->matchesRegularExpression($expected));

        $provider = new LogProvider($loggerMock);
        $provider->log($event, $data);

    }

    public function provideDifferentEvents(): array
    {
        return [
            'retrieve_header log event' => [
                'retrieve_header',
                'fr-CH,fr;q=0.9,en;q=0.8,de;q=0.7,*;q=0.5',
                '/fr-CH,fr;q=0.9/',
            ],
            'retrieve_raw_languages log event' => [
                'retrieve_raw_languages',
                [Language::create('fr-CH', 1.0), Language::create('wrong', 0)],
                '/wrong.*invalid/',
            ],
            'retrieve_normalized_languages log event' => [
                'retrieve_normalized_languages',
                [Language::create('fr-CH', 1.0), Language::create('fr', 0.9), Language::create('en', 0.8)],
                '/fr-CH.*0.8/',
            ],
            'retrieve_preferred_languages log event' => [
                'retrieve_preferred_languages',
                [Language::create('fr-CH', 1.0), Language::create('fr', 0.9), Language::create('en', 0.8)],
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
