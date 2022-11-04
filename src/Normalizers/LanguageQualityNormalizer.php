<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageQualityNormalizer implements AbstractQualityNormalizer
{
    /**
     * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred and
     * 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
     */
    private const NOT_ACCEPTABLE_QUALITY = 0;

    /**
     * 'allow_empty' A boolean that defines whether to handle an empty quality when a fallback is available.
     *
     * @var array{'allow_empty': bool}
     */
    private array $options = [
        'allow_empty' => true,
    ];

    /**
     * @param array $options
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
        /**
         * If no "q" parameter is present, the default weight is 1. See RFC 7231, Section 5.3.1.
         */
        if ($this->isUndefinedQuality($quality)) {
            return $this->prepareQuality(1);
        }

        if ($this->isValidQuality($quality)) {
            return $this->prepareQuality($quality);
        }

        return self::NOT_ACCEPTABLE_QUALITY;
    }

    private function isUndefinedQuality($quality): bool
    {
        return is_null($quality);
    }

    private function isValidQuality($value): bool
    {
        /**
         * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
         * and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
         */
        return is_numeric($value) &&
            $value > 0 &&
            max(min($value, 1), 0.001) === $value;
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

        /**
         * Since some clients may omit the quality parameter (the value after "q=" in a request header field) and
         * this is not a serious mistake, we might want to handle this empty value when a fallback is available.
         */
        if ($this->isEmptyQuality($quality)) {
            return $this->prepareQuality(
                $this->prepareFallback($fallback)
            );
        }

        if ($this->isValidQuality($quality)) {
            return $this->prepareQuality($quality);
        }

        return self::NOT_ACCEPTABLE_QUALITY;
    }

    /**
     * @return int|float
     */
    private function prepareFallback(float $fallback)
    {
        return $this->isValidQuality($fallback) ? $fallback : self::NOT_ACCEPTABLE_QUALITY;
    }

    private function isEmptyQuality($quality): bool
    {
        return is_string($quality) && trim((string)$quality) === '' && $this->options['allow_empty'];
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
}
