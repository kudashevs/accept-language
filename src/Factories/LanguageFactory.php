<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Factories;

use Kudashevs\AcceptLanguage\Exceptions\InvalidFactoryArgumentException;
use Kudashevs\AcceptLanguage\Normalizers\AbstractQualityNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\AbstractTagNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\LanguageQualityNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\LanguageTagNormalizer;
use Kudashevs\AcceptLanguage\ValueObjects\Language;

class LanguageFactory
{
    protected AbstractTagNormalizer $tagNormalizer;

    protected AbstractQualityNormalizer $qualityNormalizer;

    public function __construct(array $options = [])
    {
        $this->initNormalizers($options);
    }

    protected function initNormalizers(array $options): void
    {
        $this->tagNormalizer = $this->createTagNormalizer($options);
        $this->qualityNormalizer = $this->createQualityNormalizer($options);
    }

    protected function createTagNormalizer(array $options): AbstractTagNormalizer
    {
        return new LanguageTagNormalizer($options);
    }

    protected function createQualityNormalizer(array $options): AbstractQualityNormalizer
    {
        return new LanguageQualityNormalizer($options);
    }

    public function makeFromLanguageRange(array $rawLanguageRange, float $fallbackQuality): Language
    {
        $this->checkValidLanguageRange($rawLanguageRange);

        $currentLanguageRateSize = count($rawLanguageRange);
        $expectedLanguageRangeSize = 2;

        if ($currentLanguageRateSize > $expectedLanguageRangeSize) {
            $possibleTag = (string)$rawLanguageRange[0];
            $possibleQuality = $rawLanguageRange[1];

            return $this->createInvalidLanguage($possibleTag, $possibleQuality);
        }

        $tag = $rawLanguageRange[0];
        $quality = $rawLanguageRange[1] ?? null;

        return $this->createValidLanguageWithFallback($tag, $quality, $fallbackQuality);
    }

    protected function checkValidLanguageRange(array $range): void
    {
        if (count($range) === 0) {
            throw new InvalidFactoryArgumentException('Cannot process an empty language range.');
        }
    }

    public function createInvalidLanguage(string $tag, $quality): Language
    {
        return Language::createInvalid($tag, $quality);
    }

    /**
     * @param string $tag
     * @param int|float $quality
     * @param float $fallbackQuality
     * @return Language
     */
    public function createValidLanguageWithFallback(string $tag, $quality, float $fallbackQuality): Language
    {
        $normalizedTag = $this->tagNormalizer->normalize($tag);
        $normalizedQuality = $this->qualityNormalizer->normalizeWithFallback($quality, $fallbackQuality);

        return Language::create($normalizedTag, $normalizedQuality);
    }
}
