<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Loggers;

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
}
