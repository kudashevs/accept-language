<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Language\Language;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveNormalizedLanguagesLogPresenter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrieveNormalizedLanguagesLogPresenterTest extends TestCase
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

        $handler = new RetrieveNormalizedLanguagesLogPresenter($loggerMock);
        $handler->present('retrieve_normalized_languages', [Language::create('fr-CH', 1), Language::create('fr', 0.9)]);
    }
}
