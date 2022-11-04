<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

/**
 * AbstractQualityNormalizer represents an abstraction that normalizes a quality value to a specification.
 * If the quality does not match the certain specification the default quality value might be used.
 */
interface AbstractQualityNormalizer
{
    /**
     * Perform a normalization process and return a normalized quality.
     *
     * @param int|float|string $quality
     * @return int|float
     */
    public function normalize($quality);

    /**
     * Perform a normalization process and return a normalized quality.
     * If the quality is not valid the fallback quality value might be used.
     *
     * @param int|float|string $quality
     * @param float $fallback
     * @return int|float
     */
    public function normalizeWithFallback($quality, float $fallback);
}
