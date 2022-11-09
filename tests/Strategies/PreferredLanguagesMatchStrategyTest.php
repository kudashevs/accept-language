<?php

namespace Kudashevs\AcceptLanguage\Tests\Strategies;

use Kudashevs\AcceptLanguage\Strategies\PreferredLanguagesMatchStrategy;

class PreferredLanguagesMatchStrategyTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_the_matching_language()
    {
        $languages = [
            $this->createLanguage('fr-CH', 0.5),
        ];

        $accepted = [
            $this->createLanguage('fr-CH'),
        ];

        $strategy = new PreferredLanguagesMatchStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['fr-CH', 0.5], $result);
    }

    /** @test */
    public function it_can_retrieve_the_weak_matching_language()
    {
        $languages = [
            $this->createLanguage('fr-Latn-CH', 0.5),
        ];

        $accepted = [
            $this->createLanguage('fr-CH'),
        ];

        $strategy = new PreferredLanguagesMatchStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['fr-CH', 0.5], $result);
    }

    /** @test */
    public function it_can_retrieve_the_matching_languages_from_languages()
    {
        $languages = [
            $this->createLanguage('fr', 1),
            $this->createLanguage('fr-CH', 0.9),
            $this->createLanguage('en-US', 0.8),
            $this->createLanguage('en', 0.5),
        ];

        $accepted = [
            $this->createLanguage('fr'),
            $this->createLanguage('en-us'),
        ];

        $strategy = new PreferredLanguagesMatchStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(3, $result);
        $this->assertContainsLanguage(['fr', 1], $result);
        $this->assertContainsLanguage(['fr', 0.9], $result);
        $this->assertContainsLanguage(['en-US', 0.8], $result);
    }

    /** @test */
    public function it_can_retrieve_the_matching_languages_from_similar_languages()
    {
        $languages = [
            $this->createLanguage('fr', 1),
            $this->createLanguage('fr-CH', 0.9),
            $this->createLanguage('fr-Latn-CH', 0.8),
            $this->createLanguage('fr-fsl-Latn-CH', 0.7),
        ];

        $accepted = [
            $this->createLanguage('fr-CH'),
        ];

        $strategy = new PreferredLanguagesMatchStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(3, $result);
        $this->assertContainsLanguage(['fr-CH', 0.9], $result);
        $this->assertContainsLanguage(['fr-CH', 0.8], $result);
        $this->assertContainsLanguage(['fr-CH', 0.7], $result);
    }

    /** @test */
    public function it_can_retrieve_the_weak_matching_language_from_languages()
    {
        $languages = [
            $this->createLanguage('de', 1),
            $this->createLanguage('de-CH', 0.9),
            $this->createLanguage('de-Latn-AT', 0.8),
            $this->createLanguage('en', 0.5),
        ];

        $accepted = [
            $this->createLanguage('de-AT'),
        ];

        $strategy = new PreferredLanguagesMatchStrategy();
        $result = $strategy->retrieve($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['de-AT', 0.8], $result);
    }
}
