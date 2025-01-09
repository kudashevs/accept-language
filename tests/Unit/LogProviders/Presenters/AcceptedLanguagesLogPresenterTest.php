<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\AcceptedLanguagesLogPresenter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AcceptedLanguagesLogPresenterTest extends TestCase
{
    #[Test]
    public function it_can_present_an_event_with_data(): void
    {
        $handler = new AcceptedLanguagesLogPresenter('retrieve_accepted_languages');
        $presentation = $handler->present(
            [DefaultLanguage::create('fr-CH', 1), DefaultLanguage::create('fr', 0.9)],
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_accepted_languages/', $presentation);
    }

    #[Test]
    public function it_can_present_an_event_with_empty_data(): void
    {
        $presenter = new AcceptedLanguagesLogPresenter('retrieve_accepted_languages');
        $presentation = $presenter->present([]);

        $this->assertStringContainsString('empty', $presentation);
    }
}
