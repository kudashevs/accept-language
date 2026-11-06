<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Factories;

use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

final class LanguageFactory
{
    /*
     * If no "q" parameter is present, the default weight is 1. See RFC 9110, Section 12.4.2.
     * Languages with equal weights keep the header order, because the sorting is stable.
     */
    public const DEFAULT_FOR_NO_QUALITY = 1;

    private const DEFAULT_FOR_INVALID_QUALITY = 0;

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
     * @return LanguageInterface
     *
     */
    public function makeFromLanguageRange(array $rawLanguageRange): LanguageInterface
    {
        // handles the situation when the language range is empty
        if (count($rawLanguageRange) === 0) {
            return DefaultLanguage::createInvalid('', self::DEFAULT_FOR_INVALID_QUALITY, $this->options);
        }

        // handles the situation when the language range is suspicious
        if (count($rawLanguageRange) > 2) {
            $possibleTag = (string)$rawLanguageRange[0];
            $possibleQuality = $rawLanguageRange[1];

            return DefaultLanguage::createInvalid($possibleTag, $possibleQuality, $this->options);
        }

        // handles the situations when the language range is invalid
        if (!preg_match('/^[a-zA-Z]{1,8}(?:-[a-zA-Z0-9]{1,8})*$/i', (string)$rawLanguageRange[0])) {
            return DefaultLanguage::createInvalid((string)$rawLanguageRange[0], $rawLanguageRange[1], $this->options);
        }

        // handles the situations when the quality is invalid
        if (
            !empty($rawLanguageRange[1])
            && !preg_match(
                '/^(?:0(?:\.[0-9]{0,3})?|1(?:\.0{0,3})?)$/',
                (string)$rawLanguageRange[1]
            )) {
            return DefaultLanguage::createInvalid((string)$rawLanguageRange[0], $rawLanguageRange[1], $this->options);
        }

        /*
         * For more information about possible types of $tag and $quality variables,
         * @see \Kudashevs\AcceptLanguage\Languages\DefaultLanguage::create()
         */
        $tag = (string)$rawLanguageRange[0];
        $quality = $rawLanguageRange[1] ?? self::DEFAULT_FOR_NO_QUALITY;

        return DefaultLanguage::create($tag, $quality, $this->options);
    }
}
