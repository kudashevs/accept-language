<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageQualityNormalizer implements QualityNormalizerInterface
{
    /**
     * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
     * and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
     */
    private const NOT_ACCEPTABLE_QUALITY = 0;

    /**
     * 'allow_empty' A boolean that defines whether to handle an empty quality when a fallback is available.
     *
     * @var array{allow_empty: bool}
     */
    private array $options = [
        'allow_empty' => true,
    ];

    /**
     * @param array<string, bool|string> $options
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
    }

    private function initOptions(array $options): void
    {
        $allowed = array_intersect_key($options, $this->options);

        $this->options = array_merge($this->options, $allowed);
    }

    /**
     * @inheritDoc
     */
    public function normalize($quality)
    {
        if ($this->isUndefinedQuality($quality)) {
            return $this->generateForUndefined();
        }

        if ($this->isValidQuality($quality)) {
            return $this->generateForValid($quality);
        }

        return self::NOT_ACCEPTABLE_QUALITY;
    }

    private function isUndefinedQuality($quality): bool
    {
        return is_null($quality);
    }

    private function generateForUndefined()
    {
        // If no "q" parameter is present, the default weight is 1. See RFC 7231, Section 5.3.1.
        $quality = 1;

        if ($this->isValidFallback()) {
            $quality = $this->options['fallback'];
        }

        return $this->prepareQuality($quality);
    }

    private function isValidFallback(): bool
    {
        return isset($this->options['fallback'])
            && $this->isValidQuality($this->options['fallback']);
    }

    private function isValidQuality($quality): bool
    {
        // The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
        // and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
        return is_numeric($quality)
            && $quality > 0
            && max(min($quality, 1), 0.001) === $quality;
    }

    /**
     * @param int|float|string|null $quality
     * @return int|float
     */
    private function generateForValid($quality)
    {
        return $this->prepareQuality($quality);
    }

    /**
     * @inheritDoc
     */
    public function normalizeWithFallback($quality, float $fallback)
    {
        if ($this->isUndefinedQuality($quality)) {
            return $this->prepareQuality(
                $this->prepareFallback($fallback)
            );
        }

        // Since some clients may omit the quality parameter (the value after "q=" in a request header field) and
        // this is not a serious mistake, we might want to handle this empty value when a fallback is available.
        if ($this->isEmptyQuality($quality)) {
            return $this->generateForEmpty($fallback);
        }

        if ($this->isValidQuality($quality)) {
            return $this->generateForValid($quality);
        }

        return self::NOT_ACCEPTABLE_QUALITY;
    }

    private function isEmptyQuality($quality): bool
    {
        return is_string($quality)
            && trim((string)$quality) === ''
            && $this->options['allow_empty'];
    }

    /**
     * @return int|float
     */
    private function generateForEmpty(float $fallback)
    {
        $quality = self::NOT_ACCEPTABLE_QUALITY;

        if ($this->isValidQuality($fallback)) {
            $quality = $fallback;
        }

        return $this->prepareQuality($quality);
    }

    /**
     * @return int|float
     */
    private function prepareQuality($quality)
    {
        if ($quality === 0 || (int)$quality === 1) {
            return (int)$quality;
        }

        return (float)$quality;
    }

    /**
     * @return int|float
     */
    private function prepareFallback(float $fallback)
    {
        return $this->isValidQuality($fallback) ? $fallback : self::NOT_ACCEPTABLE_QUALITY;
    }
}
