<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\LogHandlers;

use Kudashevs\AcceptLanguage\Language\Language;
use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrieveNormalizedLanguagesLogHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrieveNormalizedLanguagesLogHandlerTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_event()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->matchesRegularExpression('/fr-CH.*retrieve_normalized_languages/')
            );

        $handler = new RetrieveNormalizedLanguagesLogHandler($loggerMock);
        $handler->handle('retrieve_normalized_languages', [Language::create('fr-CH', 1), Language::create('fr', 0.9)]);
    }
}
