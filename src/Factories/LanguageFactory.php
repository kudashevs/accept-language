<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Factories;

use Kudashevs\AcceptLanguage\Exceptions\InvalidFactoryArgumentException;
use Kudashevs\AcceptLanguage\Language\AbstractLanguage;
use Kudashevs\AcceptLanguage\Language\Language;

class LanguageFactory
{
    protected array $options = [];

    /**
     * @param array<string, bool|string> $options
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
    }

    protected function initOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param string $language
     * @param float $quality
     * @return AbstractLanguage
     */
    public function makeFromLanguageString(string $language, float $quality = 1): AbstractLanguage
    {
        return $this->createValidLanguage($language, $quality);
    }

    /**
     * @param array<string, mixed> $rawLanguageRange
     * @param float $fallbackQuality
     * @return AbstractLanguage
     *
     * @throws InvalidFactoryArgumentException
     */
    public function makeFromLanguageRange(array $rawLanguageRange, float $fallbackQuality): AbstractLanguage
    {
        $this->checkValidLanguageRange($rawLanguageRange);

        if ($this->isSuspiciousLanguageRange($rawLanguageRange)) {
            $possibleTag = (string)$rawLanguageRange[0];
            $possibleQuality = $rawLanguageRange[1];

            return $this->createInvalidLanguage($possibleTag, $possibleQuality);
        }

        $tag = $rawLanguageRange[0];
        $quality = $rawLanguageRange[1] ?? null;

        return $this->createValidLanguageWithFallback($tag, $quality, $fallbackQuality);
    }

    protected function checkValidLanguageRange(array $range): void
    {
        if (count($range) === 0) {
            throw new InvalidFactoryArgumentException('Cannot process an empty language range.');
        }
    }

    protected function isSuspiciousLanguageRange(array $range): bool
    {
        return count($range) > 2;
    }

    /**
     * @param int|float|string $quality
     */
    protected function createValidLanguage(string $tag, $quality): Language
    {
        return Language::create($tag, $quality, $this->options);
    }

    /**
     * @param mixed $quality
     */
    protected function createInvalidLanguage(string $tag, $quality): Language
    {
        return Language::createInvalid($tag, $quality, $this->options);
    }

    /**
     * @param int|float|string|null $quality
     */
    protected function createValidLanguageWithFallback(string $tag, $quality, float $fallbackQuality): Language
    {
        $options = array_merge($this->options, [
            'fallback_value' => $fallbackQuality,
        ]);

        return Language::create($tag, $quality, $options);
    }
}
