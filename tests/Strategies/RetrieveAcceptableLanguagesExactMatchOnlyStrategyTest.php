<?php

namespace Kudashevs\AcceptLanguage\Tests\Strategies;

use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Language\AbstractLanguage;
use Kudashevs\AcceptLanguage\Strategies\RetrieveAcceptableLanguagesExactMatchOnlyStrategy;
use PHPUnit\Framework\Exception;
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

    protected function createLanguage(string $language, float $quality = 1): AbstractLanguage
    {
        return (new LanguageFactory())->makeFromLanguageString($language, $quality);
    }

    /**
     * @param AbstractLanguage|array|string $needle
     * @param array<AbstractLanguage> $haystack
     * @return void
     */
    protected function assertContainsLanguage($needle, array $haystack): void
    {
        if (is_a($needle, AbstractLanguage::class)) {
            $found = array_filter($haystack, function ($language) use ($needle) {
                return $language->getTag() === $needle->getTag() &&
                    $language->getQuality() === $needle->getQuality();
            });
        }

        if (is_array($needle) && is_string($needle[0]) && is_float($needle[1])) {
            $found = array_filter($haystack, function ($language) use ($needle) {
                return $language->getTag() === $needle[0] &&
                    $language->getQuality() === $needle[1];
            });
        }

        if (is_string($needle)) {
            $found = array_filter($haystack, function ($language) use ($needle) {
                return $language->getTag() === $needle;
            });
        }

        if (!isset($found)) {
            throw new Exception('The needle of a wrong type.');
        }

        $this->assertCount(1, $found);
    }
}
