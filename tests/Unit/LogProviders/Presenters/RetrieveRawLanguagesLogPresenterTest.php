<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Language\Language;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveRawLanguagesLogPresenter;
use PHPUnit\Framework\TestCase;

class RetrieveRawLanguagesLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $presenter = new RetrieveRawLanguagesLogPresenter();
        $presentation = $presenter->present(
            'retrieve_raw_languages',
            [Language::create('fr-CH', 1), Language::create('fr', 0.9)],
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_raw_languages/', $presentation);
    }

    /** @test */
    public function it_can_present_an_event_with_empty_data()
    {
        $handler = new RetrieveRawLanguagesLogPresenter();
        $presentation = $handler->present('retrieve_raw_languages', []);

        $this->assertStringContainsString('empty', $presentation);
    }
}
