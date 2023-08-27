<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveHeaderLogPresenter;
use PHPUnit\Framework\TestCase;

class RetrieveHeaderLogPresenterTest extends TestCase
{
    /** @test */
    public function it_can_present_an_event_with_data()
    {
        $presenter = new RetrieveHeaderLogPresenter();
        $presentation = $presenter->present(
            'retrieve_header',
            'fr-CH,fr;q=0.9',
        );

        $this->assertMatchesRegularExpression('/fr-CH.*retrieve_header/', $presentation);
    }
}
