<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\HeaderLogPresenter;
use PHPUnit\Framework\TestCase;

class HeaderLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data(): void
    {
        $presenter = new HeaderLogPresenter('retrieve_header');
        $presentation = $presenter->present('fr-CH,fr;q=0.9');

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_header/', $presentation);
    }

    /** @test */
    public function it_can_present_an_event_with_empty_data(): void
    {
        $presenter = new HeaderLogPresenter('retrieve_header');
        $presentation = $presenter->present('');

        $this->assertStringContainsString('Warning', $presentation);
    }
}
