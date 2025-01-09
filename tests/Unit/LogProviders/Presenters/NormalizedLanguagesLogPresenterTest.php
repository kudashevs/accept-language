<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\NormalizedLanguagesLogPresenter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NormalizedLanguagesLogPresenterTest extends TestCase
{
    #[Test]
    public function it_can_present_an_event_with_data(): void
    {
        $handler = new NormalizedLanguagesLogPresenter('retrieve_normalized_languages');
        $presentation = $handler->present(
            [DefaultLanguage::create('fr-CH', 1), DefaultLanguage::create('fr', 0.9)],
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_normalized_languages/', $presentation);
    }

    #[Test]
    public function it_can_present_an_event_with_empty_data(): void
    {
        $presenter = new NormalizedLanguagesLogPresenter('retrieve_normalized_languages');
        $presentation = $presenter->present([]);

        $this->assertStringContainsString('empty', $presentation);
    }
}
