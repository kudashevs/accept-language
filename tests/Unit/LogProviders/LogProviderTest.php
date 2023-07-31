<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Language\Language;
use Kudashevs\AcceptLanguage\Loggers\DummyLogger;
use Kudashevs\AcceptLanguage\LogProviders\LogProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogProviderTest extends TestCase
{
    /** @test */
    public function it_can_throw_an_exception_when_a_wrong_event_provided()
    {
        $this->expectException(InvalidLogEventName::class);
        $this->expectExceptionMessage('wrong');

        $provider = new LogProvider(new DummyLogger());
        $provider->log('wrong', '');
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
