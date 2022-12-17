<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Strategies;

use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Language\AbstractLanguage;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    /**
     * @param string $language
     * @param int|float $quality
     * @return AbstractLanguage
     */
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
            throw new Exception('The needle of a wrong type.');
        }

        $this->assertCount(1, $found);
    }
}
