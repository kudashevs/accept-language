<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\NormalizedLanguagesLogPresenter;
use PHPUnit\Framework\TestCase;

class NormalizedLanguagesLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $handler = new NormalizedLanguagesLogPresenter('retrieve_normalized_languages');
        $presentation = $handler->present(
            [DefaultLanguage::create('fr-CH', 1), DefaultLanguage::create('fr', 0.9)],
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_normalized_languages/', $presentation);
    }

    /** @test */
    public function it_can_present_an_event_with_empty_data()
    {
        $presenter = new NormalizedLanguagesLogPresenter('retrieve_normalized_languages');
        $presentation = $presenter->present([]);

        $this->assertStringContainsString('empty', $presentation);
    }
}
