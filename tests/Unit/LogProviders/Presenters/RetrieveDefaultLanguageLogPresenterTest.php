<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveDefaultLanguageLogPresenter;
use PHPUnit\Framework\TestCase;

class RetrieveDefaultLanguageLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $presenter = new RetrieveDefaultLanguageLogPresenter('retrieve_default_language');
        $presentation = $presenter->present('fr-CH,fr;q=0.9');

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_default_language/', $presentation);
    }

    /** @test */
    public function it_can_present_an_event_with_empty_data()
    {
        $presenter = new RetrieveDefaultLanguageLogPresenter('retrieve_default_language');
        $presentation = $presenter->present('');

        $this->assertStringContainsString('Warning', $presentation);
    }
}
