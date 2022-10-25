<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageQualityNormalizer implements AbstractQualityNormalizer
{
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
            return 0;
        }

        return (float)$quality;
    }
}
