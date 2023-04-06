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
        $provider->log('wrong', []);
    }

    /** @test */
    public function it_can_handle_the_retrieve_header_event()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with($this->stringContains('fr-CH,fr'));

        $provider = new LogProvider($loggerMock);
        $provider->log('retrieve_header', 'fr-CH,fr;q=0.9,en;q=0.8,de;q=0.7,*;q=0.5');
    }

}
