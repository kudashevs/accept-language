<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

use Kudashevs\AcceptLanguage\Normalizers\AbstractQualityNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\LanguageQualityNormalizer;

class QualityValue
{
    private AbstractQualityNormalizer $normalizer;

    private float $fallback;

    private $quality; // @todo add int|float

    private bool $valid = true;

    /**
     * @param int|float|string $quality
     * @param array $options
     */
    public function __construct($quality, array $options = [])
    {
        $this->initNormalizer($options);
        $this->initFallback($options);

        $this->initQuality($quality);
    }

    private function initNormalizer(array $options): void
    {
        $this->normalizer = $this->createQualityNormalizer($options);
    }

    private function createQualityNormalizer(array $options): AbstractQualityNormalizer
    {
        return new LanguageQualityNormalizer($options);
    }

    private function initFallback(array $options): void
    {
        $this->fallback = $options['fallback_value'] ?? 0;
    }

    private function initQuality($quality): void
    {
        if (!$this->isValidQuality($quality)) {
            $this->valid = false;
            $this->quality = $this->prepareQuality($quality);

            return;
        }

        $this->quality = $this->normalizeQuality($quality);
    }

    private function isValidQuality($quality): bool
    {
        return is_null($quality) ||
            $this->isEmptyQuality($quality) ||
            $this->isInValidRange($quality);
    }

    private function isEmptyQuality($quality): bool
    {
        return is_string($quality) && trim((string)$quality) === '';
    }

    private function isInValidRange($quality): bool
    {
        return is_numeric($quality) && $quality >= 0 && $quality <= 1;
    }

    private function prepareQuality($quality)
    {
        if ($this->isPlainString($quality) || $this->isLikeInteger($quality)) {
            return (int)$quality;
        }

        return (float)$quality;
    }

    private function isPlainString($quality): bool
    {
        return is_string($quality) && !is_numeric($quality);
    }

    private function isLikeInteger($quality): bool
    {
        return is_int($quality) || (
                is_string($quality) &&
                is_numeric($quality) &&
                strpos($quality, '.') === false
            );
    }

    private function normalizeQuality($quality)
    {
        if ($this->isFallbackable()) {
            return $this->normalizer->normalizeWithFallback($quality, $this->fallback);
        }

        return $this->normalizer->normalize($quality);
    }

    private function isFallbackable(): bool
    {
        return $this->fallback > 0;
    }

    /**
     * @return int|float
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
}
