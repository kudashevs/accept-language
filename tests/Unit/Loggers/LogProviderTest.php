<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Loggers;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLoggableEvent;
use Kudashevs\AcceptLanguage\Loggers\DummyLogger;
use Kudashevs\AcceptLanguage\Loggers\LogProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogProviderTest extends TestCase
{
    /** @test */
    public function it_can_throw_an_exception_when_a_wrong_event_provided()
    {
        $this->expectException(InvalidLoggableEvent::class);
        $this->expectExceptionMessage('wrong');

        $provider = new LogProvider(new DummyLogger());
        $provider->log('wrong', '');
    }

    /** @test */
    public function it_can_skip_an_event_when_it_is_disabled()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())
            ->method('info');

        $provider = new LogProvider($loggerMock, [
            'retrieve_header' => false,
        ]);

        $provider->log('retrieve_header', 'anything');
    }

    /**
     * @test
     * @dataProvider provideDifferentEvents
     */
    public function it_can_handle_an_event(string $event, string $data, string $expected): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with($this->stringContains($expected));

        $provider = new LogProvider($loggerMock);
        $provider->log($event, $data);

    }

    public function provideDifferentEvents(): array
    {
        return [
            'the retrieve_header event' => [
                'retrieve_header',
                'accept_header',
                'accept_header',
            ],
            'the retrieve_raw_languages event' => [
                'retrieve_raw_languages',
                'raw_languages',
                'raw_languages',
            ],
            'the retrieve_normalized_languages event' => [
                'retrieve_normalized_languages',
                'normalized_languages',
                'normalized_languages',
            ],
            'the retrieve_preferred_languages event' => [
                'retrieve_preferred_languages',
                'preferred_languages',
                'preferred_languages',
            ],
            'the retrieve_preferred_language event' => [
                'retrieve_preferred_language',
                'preferred_language',
                'preferred_language',
            ],
        ];
    }
}
