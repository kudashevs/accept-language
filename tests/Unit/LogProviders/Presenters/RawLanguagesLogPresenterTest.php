<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Languages\Language;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RawLanguagesLogPresenter;
use PHPUnit\Framework\TestCase;

class RawLanguagesLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $presenter = new RawLanguagesLogPresenter('retrieve_raw_languages');
        $presentation = $presenter->present(
            [Language::create('fr-CH', 1), Language::create('fr', 0.9)],
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_raw_languages/', $presentation);
    }

    /** @test */
    public function it_can_present_an_event_with_empty_data()
    {
        $presenter = new RawLanguagesLogPresenter('retrieve_raw_languages');
        $presentation = $presenter->present([]);

        $this->assertStringContainsString('empty', $presentation);
    }
}
