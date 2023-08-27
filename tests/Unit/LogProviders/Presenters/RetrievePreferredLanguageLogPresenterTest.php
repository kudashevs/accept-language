<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrievePreferredLanguageLogPresenter;
use PHPUnit\Framework\TestCase;

class RetrievePreferredLanguageLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $presenter = new RetrievePreferredLanguageLogPresenter();
        $presentation = $presenter->present(
            'retrieve_preferred_language',
            'fr-CH,fr;q=0.9',
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_preferred_language/', $presentation);
    }
}
