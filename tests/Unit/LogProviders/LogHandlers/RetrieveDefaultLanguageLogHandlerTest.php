<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\LogHandlers;

use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrieveDefaultLanguageLogHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrieveDefaultLanguageLogHandlerTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_event()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->matchesRegularExpression('/fr-CH.*retrieve_default_language/')
            );

        $handler = new RetrieveDefaultLanguageLogHandler($loggerMock);
        $handler->handle('retrieve_default_language', 'fr-CH,fr;q=0.9');
    }
}
