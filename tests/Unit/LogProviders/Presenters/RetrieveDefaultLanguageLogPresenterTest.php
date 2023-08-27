<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveDefaultLanguageLogPresenter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrieveDefaultLanguageLogPresenterTest extends TestCase
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

        $handler = new RetrieveDefaultLanguageLogPresenter($loggerMock);
        $handler->present('retrieve_default_language', 'fr-CH,fr;q=0.9');
    }
}
