<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Strategies;

use Kudashevs\AcceptLanguage\Strategies\ExactMatchStrategy;
use PHPUnit\Framework\Attributes\Test;

class ExactMatchStrategyTest extends StrategyTestCase
{
    #[Test]
    public function it_can_retrieve_the_exact_matching_language(): void
    {
        $languages = [
            $this->createLanguage('fr-CH', 0.5),
        ];

        $accepted = [
            $this->createLanguage('fr-CH'),
        ];

        $strategy = new ExactMatchStrategy();
        $result = $strategy->match($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['fr-CH', 0.5], $result);
    }

    #[Test]
    public function it_can_retrieve_the_exact_matching_language_from_languages(): void
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

        $strategy = new ExactMatchStrategy();
        $result = $strategy->match($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['fr-CH', 0.9], $result);
    }

    #[Test]
    public function it_can_retrieve_the_exact_matching_language_from_similar_languages(): void
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

        $strategy = new ExactMatchStrategy();
        $result = $strategy->match($languages, $accepted);

        $this->assertCount(1, $result);
        $this->assertContainsLanguage(['fr-CH', 0.9], $result);
    }

    #[Test]
    public function it_can_retrieve_the_exact_matching_languages_from_languages(): void
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

        $strategy = new ExactMatchStrategy();
        $result = $strategy->match($languages, $accepted);

        $this->assertCount(2, $result);
        $this->assertContainsLanguage(['fr-CH', 0.9], $result);
        $this->assertContainsLanguage(['en', 0.5], $result);
    }
}
