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

    public function __construct(array $options = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function normalize($quality)
    {
        return $this->normalizeQuality($quality, self::NOT_ACCEPTABLE_QUALITY);
    }

    /**
     * @inheritDoc
     */
    public function normalizeWithFallback($quality, float $fallback)
    {
        return $this->normalizeQuality($quality, $fallback);
    }

    private function normalizeQuality($quality, float $fallback)
    {
        if ($this->isValidQuality($quality)) {
            return $this->prepareQuality($quality);
        }

        if ($this->isUndefinedQuality($quality)) {
            return $this->prepareQuality($fallback);
        }

        if ($this->isEmptyQuality($quality)) {
            return $this->prepareQuality($fallback);
        }

        return self::NOT_ACCEPTABLE_QUALITY;
    }

    protected function isValidQuality($value): bool
    {
        /**
         * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
         * and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
         */
        return is_numeric($value) &&
            $value > 0 &&
            max(min($value, 1), 0.001) === $value;
    }

    private function isUndefinedQuality($quality): bool
    {
        return is_null($quality);
    }

    private function isEmptyQuality($quality): bool
    {
        return is_string($quality) && trim((string)$quality) === '';
    }

    /**
     * @return int|float
     */
    private function prepareQuality($quality)
    {
        if ($quality == 0 || $quality == 1) {
            return (int)$quality;
        }

        return (float)$quality;
    }
}
