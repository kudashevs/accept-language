<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Factories;

use Kudashevs\AcceptLanguage\Exceptions\InvalidFactoryArgument;
use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

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
     * @return LanguageInterface
     */
    public function makeFromLanguageString(string $language, float $quality = 1): LanguageInterface
    {
        return $this->createLanguage($language, $quality);
    }

    /**
     * @param array<int, string|mixed> $rawLanguageRange
     * @param float $fallbackQuality
     * @return LanguageInterface
     *
     * @throws InvalidFactoryArgument
     */
    public function makeFromLanguageRange(array $rawLanguageRange, float $fallbackQuality): LanguageInterface
    {
        if (count($rawLanguageRange) === 0) {
            return $this->createInvalidLanguage('', 0);
        }

        if ($this->isSuspiciousLanguageRange($rawLanguageRange)) {
            $possibleTag = (string)$rawLanguageRange[0];
            $possibleQuality = $rawLanguageRange[1];

            return $this->createInvalidLanguage($possibleTag, $possibleQuality);
        }

        $tag = (string)$rawLanguageRange[0];
        $quality = $rawLanguageRange[1] ?? null;

        return $this->createLanguageWithFallbackQuality($tag, $quality, $fallbackQuality);
    }

    protected function isSuspiciousLanguageRange(array $range): bool
    {
        return count($range) > 2;
    }

    /**
     * Create a Language instance. The correctness of a provided language value will be
     * determined during the validation process (the language state might be invalid).
     * @see \Kudashevs\AcceptLanguage\Languages\DefaultLanguage
     *
     * @param int|float|string $quality
     */
    protected function createLanguage(string $tag, $quality): DefaultLanguage
    {
        return DefaultLanguage::create($tag, $quality, $this->options);
    }

    /**
     * Create a Language instance with a predefined invalid language state.
     * @see \Kudashevs\AcceptLanguage\Languages\DefaultLanguage
     *
     * @param mixed $quality
     */
    protected function createInvalidLanguage(string $tag, $quality): DefaultLanguage
    {
        return DefaultLanguage::createInvalid($tag, $quality, $this->options);
    }

    /**
     * Create a Language instance. The correctness of a provided language value will be
     * determined during the validation process (the language state might be invalid).
     * @see \Kudashevs\AcceptLanguage\Languages\DefaultLanguage*
     *
     * @param int|float|string|null $quality
     */
    protected function createLanguageWithFallbackQuality(string $tag, $quality, float $fallbackQuality): DefaultLanguage
    {
        $options = array_merge($this->options, [
            'fallback_value' => $fallbackQuality,
        ]);

        return DefaultLanguage::create($tag, $quality, $options);
    }
}
