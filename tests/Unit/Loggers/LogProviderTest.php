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

    /**
     * @test
     * @dataProvider provideDifferentEvents
     */
    public function it_can_handle_an_event(string $event, string $data, string $method, string $expected): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method($method)
            ->with($this->stringContains($expected));

        $provider = new LogProvider($loggerMock);
        $provider->log($event, $data);

    }

    public function provideDifferentEvents(): array
    {
        return [
            'the retrieve_header event' => ['retrieve_header', 'en_GB', 'info', 'en_GB'],
            'the retrieve_raw_languages event' => ['retrieve_raw_languages', 'en_GB', 'info', 'en_GB'],
        ];
    }
}
