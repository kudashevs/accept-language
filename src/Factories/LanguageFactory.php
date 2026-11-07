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
        if ($this->isEmptyLanguageRange($rawLanguageRange)) {
            return DefaultLanguage::createInvalid('', self::DEFAULT_FOR_INVALID_QUALITY, $this->options);
        }

        if ($this->isSuspiciousLanguageRange($rawLanguageRange)) {
            $possibleTag = (string)$rawLanguageRange[0];
            $possibleQuality = $rawLanguageRange[1];

            return DefaultLanguage::createInvalid($possibleTag, $possibleQuality, $this->options);
        }

        if ($this->hasInvalidLanguageType($rawLanguageRange)) {
            return DefaultLanguage::createInvalid('', self::DEFAULT_FOR_INVALID_QUALITY, $this->options);
        }

        if ($this->hasInvalidLanguage($rawLanguageRange)) {
            return DefaultLanguage::createInvalid(
                (string)$rawLanguageRange[0],
                $rawLanguageRange[1] ?? self::DEFAULT_FOR_INVALID_QUALITY,
                $this->options
            );
        }

        if ($this->hasEmptyQuality($rawLanguageRange)) {
            return DefaultLanguage::createInvalid(
                (string)$rawLanguageRange[0],
                self::DEFAULT_FOR_INVALID_QUALITY,
                $this->options
            );
        }

        if ($this->hasInvalidQuality($rawLanguageRange)) {
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

    /**
     * @param array $rawLanguageRange
     * @return bool
     */
    private function isEmptyLanguageRange(array $rawLanguageRange): bool
    {
        return count($rawLanguageRange) === 0;
    }

    /**
     * @param array $rawLanguageRange
     * @return bool
     */
    private function isSuspiciousLanguageRange(array $rawLanguageRange): bool
    {
        return count($rawLanguageRange) > 2;
    }

    /**
     * @param array $rawLanguageRange
     * @return bool
     */
    private function hasInvalidLanguageType(array $rawLanguageRange): bool
    {
        return isset($rawLanguageRange[0])
            && !is_scalar($rawLanguageRange[0]);
    }

    /**
     * @param $rawLanguageRange
     * @return bool
     */
    private function hasInvalidLanguage(array $rawLanguageRange): bool
    {
        return isset($rawLanguageRange[0])
            && !preg_match('/^(?:[a-zA-Z]{1,8}(?:-[a-zA-Z0-9]{1,8})*|\*)$/i', (string)$rawLanguageRange[0]);
    }

    /**
     * @param array $rawLanguageRange
     * @return bool
     */
    private function hasEmptyQuality(array $rawLanguageRange): bool
    {
        return isset($rawLanguageRange[1])
            && !is_null($rawLanguageRange[1])
            && is_string($rawLanguageRange[1])
            && strlen(trim($rawLanguageRange[1])) === 0;
    }

    /**
     * @param array $rawLanguageRange
     * @return bool
     */
    private function hasInvalidQuality(array $rawLanguageRange): bool
    {
        return isset($rawLanguageRange[1])
            && !empty($rawLanguageRange[1])
            && !preg_match(
                '/^(?:0(?:\.[0-9]{0,3})?|1(?:\.0{0,3})?)$/',
                (string)$rawLanguageRange[1]
            );
    }
}
