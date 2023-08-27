<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Language\Language;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrievePreferredLanguagesLogPresenter;
use PHPUnit\Framework\TestCase;

class RetrievePreferredLanguagesLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $presenter = new RetrievePreferredLanguagesLogPresenter();
        $presentation = $presenter->present(
            'retrieve_preferred_languages',
            [Language::create('fr-CH', 1), Language::create('fr', 0.9)],
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_preferred_languages/', $presentation);
    }
}
