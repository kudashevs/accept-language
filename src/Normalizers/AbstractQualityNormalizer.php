<?php

namespace Kudashevs\AcceptLanguage\Normalizers;

interface AbstractQualityNormalizer
{
    /**
     * Perform a normalization process and return a normalized quality.
     *
     * @param int|float|string $quality
     * @return int|float
     */
    public function normalize($quality);
}
