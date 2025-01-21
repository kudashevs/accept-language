<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Strategies;

use InvalidArgumentException;
use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Languages\LanguageInterface;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class StrategyTestCase extends PHPUnitTestCase
{
    /**
     * @param string $language
     * @param float $quality
     * @return LanguageInterface
     */
    protected function createLanguage(string $language, float $quality = 1): LanguageInterface
    {
        return (new LanguageFactory())->makeFromLanguageString($language, $quality);
    }

    /**
     * @param LanguageInterface|array|string $needle
     * @param array<LanguageInterface> $haystack
     * @return void
     */
    protected function assertContainsLanguage($needle, array $haystack): void
    {
        if (is_a($needle, LanguageInterface::class)) {
            $found = array_filter($haystack, function ($language) use ($needle) {
                return $language->getTag() === $needle->getTag() &&
                    $language->getQuality() === $needle->getQuality();
            });
        }

        if (is_array($needle) && is_string($needle[0]) && is_numeric($needle[1])) {
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
            throw new InvalidArgumentException('The needle of a wrong type.');
        }

        $this->assertCount(1, $found);
    }
}
