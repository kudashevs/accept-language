<?php

namespace Kudashevs\AcceptLanguage\Normalizers;

interface AbstractQualityNormalizer
{
    /**
     * Perform a normalization process and return a normalized quality.
     *
     * @param $quality
     * @param float $default
     * @return int|float
     */
    public function normalize($quality, float $default);
}
