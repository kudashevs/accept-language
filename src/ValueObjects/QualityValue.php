<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

use Kudashevs\AcceptLanguage\Normalizers\LanguageQualityNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\QualityNormalizerInterface;

class QualityValue
{
    private QualityNormalizerInterface $normalizer;

    private float $fallback;

    private $quality;

    private array $options = [
        'fallback' => 0,
        'allow_empty' => true,
    ];

    private bool $valid = true;

    /**
     * @param int|float|string $quality
     * @param array<string, bool|string> $options
     */
    public function __construct($quality, array $options = [])
    {
        $this->initNormalizer($options);
        $this->initOptions($options);
        $this->initFallback($options);

        $this->initQuality($quality);
    }

    /**
     * @param array<string, bool> $options
     */
    private function initOptions(array $options): void
    {
        $allowed = array_intersect_key($options, $this->options);

        $this->options = array_merge($this->options, $allowed);
    }

    private function initNormalizer(array $options): void
    {
        $this->normalizer = $this->createDefaultNormalizer($options);
    }

    private function createDefaultNormalizer(array $options): QualityNormalizerInterface
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
            $this->quality = $this->prepareQuality($quality);
            $this->valid = false;

            return;
        }

        $this->quality = $this->normalizeQuality($quality);
    }

    private function isValidQuality($quality): bool
    {
        return is_null($quality)
            || $this->isEmptyQuality($quality)
            || $this->isInsideValidRange($quality);
    }

    private function isEmptyQuality($quality): bool
    {
        return is_string($quality) && trim((string)$quality) === '';
    }

    private function isInsideValidRange($quality): bool
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
        return is_int($quality) || $this->isStringInteger($quality);
    }

    private function isStringInteger($quality): bool
    {
        return is_string($quality)
            && is_numeric($quality)
            && strpos($quality, '.') === false;
    }

    private function normalizeQuality($quality)
    {
        if ($this->isAppropriateFallback()) {
            return $this->normalizer->normalizeWithFallback($quality, $this->fallback);
        }

        return $this->normalizer->normalize($quality);
    }

    private function isAppropriateFallback(): bool
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
