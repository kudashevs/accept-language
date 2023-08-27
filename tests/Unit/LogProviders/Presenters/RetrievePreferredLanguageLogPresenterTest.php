<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrievePreferredLanguageLogPresenter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrievePreferredLanguageLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_event()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->matchesRegularExpression('/fr-CH.*retrieve_preferred_language/')
            );

        $handler = new RetrievePreferredLanguageLogPresenter($loggerMock);
        $handler->present('retrieve_preferred_language', 'fr-CH,fr;q=0.9');
    }
}
