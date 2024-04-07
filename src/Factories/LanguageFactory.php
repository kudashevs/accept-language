<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Factories;

use Kudashevs\AcceptLanguage\Exceptions\InvalidFactoryArgument;
use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

final class LanguageFactory
{
    /**
     * @var array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool} $options
     */
    private array $options = [];

    /**
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool} $options
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
    }

    /**
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool} $options
     */
    private function initOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param string $language
     * @param float $quality
     * @return LanguageInterface
     */
    public function makeFromLanguageString(string $language, float $quality = 1): LanguageInterface
    {
        return DefaultLanguage::create($language, $quality, $this->options);
    }

    /**
     * @param array<array-key, string|mixed> $rawLanguageRange
     * @param float $fallbackQuality
     * @return LanguageInterface
     *
     * @throws InvalidFactoryArgument
     */
    public function makeFromLanguageRange(array $rawLanguageRange, float $fallbackQuality): LanguageInterface
    {
        // handles the situation when the language range is empty
        if (count($rawLanguageRange) === 0) {
            return DefaultLanguage::createInvalid('', 0, $this->options);
        }

        // handles the situation when the language range is suspicious
        if (count($rawLanguageRange) > 2) {
            $possibleTag = (string)$rawLanguageRange[0];
            $possibleQuality = $rawLanguageRange[1];

            return DefaultLanguage::createInvalid($possibleTag, $possibleQuality, $this->options);
        }

        /*
         * For more information about possible types of $tag and $quality variables,
         * @see \Kudashevs\AcceptLanguage\Languages\DefaultLanguage::create()
         */
        $tag = (string)$rawLanguageRange[0];
        $quality = $rawLanguageRange[1] ?? null;
        $optionsWithFallback = array_merge($this->options, [
            'fallback' => $fallbackQuality,
        ]);

        return DefaultLanguage::create($tag, $quality, $optionsWithFallback);
    }
}
