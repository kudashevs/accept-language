<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Language;

use Kudashevs\AcceptLanguage\ValueObjects\LanguageTag;
use Kudashevs\AcceptLanguage\ValueObjects\QualityValue;

final class Language implements AbstractLanguage
{
    private array $options;

    private LanguageTag $tag;

    private QualityValue $quality;

    private bool $valid = false;

    /**
     * @param string $tag
     * @param int|float|string $quality
     * @param array $options
     * @return Language
     */
    public static function create(string $tag, $quality, array $options = []): Language
    {
        return new Language($tag, $quality, $options);
    }

    /**
     * @param string $tag
     * @param $quality
     * @param array $options
     * @return Language
     */
    public static function createInvalid(string $tag, $quality, array $options = []): Language
    {
        $language = new Language($tag, $quality, $options);
        $language->valid = false;

        return $language;
    }

    private function __construct(string $tag, $quality, array $options = []) // @todo add union int|float
    {
        $this->initOptions($options);
        $this->initLanguage($tag, $quality);
    }

    private function initOptions(array $options): void
    {
        $this->options = $options;
    }

    private function initLanguage(string $tag, $quality): void
    {
        [
            'tag' => $this->tag,
            'quality' => $this->quality,
            'valid' => $this->valid,
        ] = $this->prepareLanguageState($tag, $quality);
    }

    /**
     * @param string $tag
     * @param int|float|string $quality
     * @return array{tag: LanguageTag,quality: QualityValue,valid: bool}
     */
    private function prepareLanguageState(string $tag, $quality): array
    {
        $languageTag = new LanguageTag($tag, $this->options);
        $qualityValue = new QualityValue($quality, $this->options);
        $valid = $this->isValidState($languageTag, $qualityValue);

        return [
            'tag' => $languageTag,
            'quality' => $qualityValue,
            'valid' => $valid,
        ];
    }

    private function isValidState(LanguageTag $tag, QualityValue $quality): bool
    {
        return $tag->isValid() && $quality->isValid();
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag->getTag();
    }

    /**
     * @return int|float
     */
    public function getQuality()
    {
        return $this->quality->getQuality();
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
}
