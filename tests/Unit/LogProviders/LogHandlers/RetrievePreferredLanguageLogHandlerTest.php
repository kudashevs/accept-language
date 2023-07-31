<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\LogHandlers;

use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrievePreferredLanguageLogHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrievePreferredLanguageLogHandlerTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_event()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('retrieve_preferred_language')
            );

        $handler = new RetrievePreferredLanguageLogHandler($loggerMock);
        $handler->handle('retrieve_preferred_language', 'anything');
    }
}
