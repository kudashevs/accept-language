<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Language\Language;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveNormalizedLanguagesLogPresenter;
use PHPUnit\Framework\TestCase;

class RetrieveNormalizedLanguagesLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $handler = new RetrieveNormalizedLanguagesLogPresenter();
        $presentation = $handler->present(
            'retrieve_normalized_languages',
            [Language::create('fr-CH', 1), Language::create('fr', 0.9)],
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_normalized_languages/', $presentation);
    }

    /** @test */
    public function it_can_present_an_event_with_empty_data()
    {
        $handler = new RetrieveNormalizedLanguagesLogPresenter();
        $presentation = $handler->present('retrieve_normalized_languages', []);

        $this->assertStringContainsString('empty', $presentation);
    }
}
