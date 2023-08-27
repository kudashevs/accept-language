<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveDefaultLanguageLogPresenter;
use PHPUnit\Framework\TestCase;

class RetrieveDefaultLanguageLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $presenter = new RetrieveDefaultLanguageLogPresenter();
        $presentation = $presenter->present(
            'retrieve_default_language',
            'fr-CH,fr;q=0.9',
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_default_language/', $presentation);
    }

    /** @test */
    public function it_can_present_an_event_with_empty_data()
    {
        $handler = new RetrieveDefaultLanguageLogPresenter();
        $presentation = $handler->present('retrieve_default_language', '');

        $this->assertStringContainsString('Warning', $presentation);
    }
}
