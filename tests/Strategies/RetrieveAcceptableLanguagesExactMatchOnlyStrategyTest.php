<?php

namespace Kudashevs\AcceptLanguage\Tests\Strategies;

use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Strategies\RetrieveAcceptableLanguagesExactMatchOnlyStrategy;
use PHPUnit\Framework\TestCase;

class RetrieveAcceptableLanguagesExactMatchOnlyStrategyTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_the_exact_match_language()
    {
        $languages = [
            (new LanguageFactory())->makeFromLanguageString('fr-CH', 0.5),
        ];

        $accepted = [
            (new LanguageFactory())->makeFromLanguageString('fr-CH', 1.0),
        ];

        $strategy = new RetrieveAcceptableLanguagesExactMatchOnlyStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertSame('fr-CH', $result[0]->getTag());
        $this->assertSame(0.5, $result[0]->getQuality());
    }
}
