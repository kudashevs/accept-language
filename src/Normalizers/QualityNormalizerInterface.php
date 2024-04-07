<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

/**
 * QualityNormalizerInterface represents an abstraction that normalizes a quality value to the certain specification.
 */
interface QualityNormalizerInterface
{
    /**
     * Perform a normalization process and return a normalized quality.
     *
     * @param int|float|string|null $quality
     * @param array{allow_empty?: bool, fallback?: int|float} $options
     * @return int|float
     */
    public function normalize($quality, array $options);
}
