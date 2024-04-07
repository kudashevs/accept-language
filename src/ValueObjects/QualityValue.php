<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

use Kudashevs\AcceptLanguage\Normalizers\LanguageQualityNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\QualityNormalizerInterface;

class QualityValue
{
    private QualityNormalizerInterface $normalizer;

    private $quality;

    private bool $valid = true;

    /**
     * 'allow_empty' bool A boolean that defines whether to handle an empty quality when a valid fallback is available.
     * 'fallback' int|float An integer or a float with a valid quality fallback value. See RFC 7231, Section 5.3.1.
     *
     * @var array{fallback: int|float, allow_empty: bool}
     */
    private array $options = [
        'allow_empty' => true,
        'fallback' => -1,
    ];

    /**
     * @param int|float|string $quality
     * @param array<string, int|float|bool> $options
     */
    public function __construct($quality, array $options = [])
    {
        $this->initNormalizer();
        $this->initOptions($options);

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

    private function initNormalizer(): void
    {
        $this->normalizer = $this->createDefaultNormalizer();
    }

    private function createDefaultNormalizer(): QualityNormalizerInterface
    {
        return new LanguageQualityNormalizer();
    }

    private function initQuality($quality): void
    {
        if ($this->isInvalidQuality($quality)) {
            $this->quality = $this->prepareInvalidQuality($quality);
            $this->valid = false;

            return;
        }

        $this->quality = $this->normalizeQuality($quality);
    }

    private function isInvalidQuality($quality): bool
    {
        return !$this->isValidQuality($quality);
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

    private function prepareInvalidQuality($quality)
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
        return $this->normalizer->normalize($quality, $this->options);
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

    /**
     * @return array{allow_empty: bool, fallback: int|float}
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
