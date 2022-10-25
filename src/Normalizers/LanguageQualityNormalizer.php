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

    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function normalize($quality, float $default)
    {
        return $this->normalizeQuality($quality, $default);
    }

    private function normalizeQuality($quality, float $default)
    {
        if (!isset($quality)) {
            return self::NOT_ACCEPTABLE_QUALITY;
        }

        return (float)$quality;
    }
}
