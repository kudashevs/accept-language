<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\LogHandlers;

use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrieveRawLanguagesLogHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrieveRawLanguagesLogHandlerTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_event()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('retrieve_header')
            );

        $handler = new RetrieveRawLanguagesLogHandler($loggerMock);
        $handler->handle('retrieve_header', 'anything');
    }
}
