<?php

namespace Kudashevs\AcceptLanguage\Tests\Strategies;

use Kudashevs\AcceptLanguage\Strategies\RetrieveAcceptableLanguagesExactMatchOnlyStrategy;

class RetrieveAcceptableLanguagesExactMatchOnlyStrategyTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_the_exact_match_language()
    {
        $languages = [
            $this->createLanguage('fr-CH', 0.5),
        ];

        $accepted = [
            $this->createLanguage('fr-CH'),
        ];

        $strategy = new RetrieveAcceptableLanguagesExactMatchOnlyStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['fr-CH', 0.5], $result);
    }

    /** @test */
    public function it_can_retrieve_the_exact_match_language_from_languages()
    {
        $languages = [
            $this->createLanguage('fr', 1),
            $this->createLanguage('fr-CH', 0.9),
            $this->createLanguage('en-US', 0.8),
            $this->createLanguage('en', 0.5),
        ];

        $accepted = [
            $this->createLanguage('fr-CH'),
        ];

        $strategy = new RetrieveAcceptableLanguagesExactMatchOnlyStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['fr-CH', 0.9], $result);
    }

    /** @test */
    public function it_can_retrieve_the_exact_match_languages_from_languages()
    {
        $languages = [
            $this->createLanguage('fr', 1),
            $this->createLanguage('fr-CH', 0.9),
            $this->createLanguage('en-US', 0.8),
            $this->createLanguage('en', 0.5),
        ];

        $accepted = [
            $this->createLanguage('fr-CH'),
            $this->createLanguage('en'),
        ];

        $strategy = new RetrieveAcceptableLanguagesExactMatchOnlyStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(2, $result);
        $this->assertContainsLanguage(['fr-CH', 0.9], $result);
        $this->assertContainsLanguage(['en', 0.5], $result);
    }
}
