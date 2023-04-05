<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Loggers;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLoggableEvent;
use Kudashevs\AcceptLanguage\Loggers\DummyLogger;
use Kudashevs\AcceptLanguage\Loggers\LogProvider;
use PHPUnit\Framework\TestCase;

class LogProviderTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $provider = new LogProvider(new DummyLogger());

        $this->assertInstanceOf(LogProvider::class, $provider);
    }

    /** @test */
    public function it_can_throw_an_exception_when_a_wrong_event_provided()
    {
        $this->expectException(InvalidLoggableEvent::class);
        $this->expectExceptionMessage('wrong');

        $provider = new LogProvider(new DummyLogger());
        $provider->log('wrong', []);
    }
}
