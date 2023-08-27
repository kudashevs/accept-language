<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveHeaderLogPresenter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrieveHeaderLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_event()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                $this->matchesRegularExpression('/fr-CH.*retrieve_header/')
            );

        $handler = new RetrieveHeaderLogPresenter($loggerMock);
        $handler->present('retrieve_header', 'fr-CH,fr;q=0.9');
    }
}
