<?php

namespace Kudashevs\AcceptLanguage\Tests\Strategies;

use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Language\AbstractLanguage;
use Kudashevs\AcceptLanguage\Strategies\RetrieveAcceptableLanguagesExactMatchOnlyStrategy;
use PHPUnit\Framework\TestCase;

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

        $this->assertSame('fr-CH', $result[0]->getTag());
        $this->assertSame(0.5, $result[0]->getQuality());
    }

    protected function createLanguage(string $language, float $quality = 1): AbstractLanguage
    {
        return (new LanguageFactory())->makeFromLanguageString($language, $quality);
    }
}
