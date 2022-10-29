<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Converters;

use Kudashevs\AcceptLanguage\Exceptions\InvalidConverterArgumentException;
use Kudashevs\AcceptLanguage\Normalizers\AbstractQualityNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\AbstractTagNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\LanguageQualityNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\LanguageTagNormalizer;
use Kudashevs\AcceptLanguage\ValueObjects\Language;

class ArrayToLanguageConverter implements AbstractToLanguageConverter
{
    protected const EXPECTED_ARRAY_SIZE = 2;

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

    /**
     * @param array $data
     * @param float $quality
     * @return Language
     */
    public function convert($data, float $quality): Language
    {
        $this->checkValidDataSource($data);

        return $this->convertArrayToLanguage($data, $quality);
    }

    protected function checkValidDataSource($data): void
    {
        if (!is_array($data)) {
            throw new InvalidConverterArgumentException('An argument of a wrong type. An array is expected.');
        }

        if (count($data) === 0) {
            throw new InvalidConverterArgumentException('Cannot process an empty array.');
        }
    }

    public function convertArrayToLanguage(array $data, float $fallbackQuality): Language
    {
        if (count($data) > self::EXPECTED_ARRAY_SIZE) {
            return $this->makeLanguage($data[0], 0);
        }

        return $this->makeLanguage($data[0], $fallbackQuality);
    }

    /**
     * @param string $tag
     * @param int|float $quality
     * @return Language
     */
    public function makeLanguage(string $tag, $quality): Language
    {
        $normalizedTag = $this->tagNormalizer->normalize($tag);
        $normalizedQuality = $this->qualityNormalizer->normalize($quality);

        return new Language($normalizedTag, $normalizedQuality);
    }
}
