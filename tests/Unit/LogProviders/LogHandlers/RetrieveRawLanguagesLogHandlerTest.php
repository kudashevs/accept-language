<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\LogHandlers;

use Kudashevs\AcceptLanguage\Language\Language;
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
                $this->matchesRegularExpression('/any.*retrieve_raw_languages/')
            );

        $handler = new RetrieveRawLanguagesLogHandler($loggerMock);
        $handler->handle('retrieve_raw_languages', [Language::create('any', 1)]);
    }
}
