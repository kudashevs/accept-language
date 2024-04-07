<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Languages;

use Kudashevs\AcceptLanguage\ValueObjects\LanguageTag;
use Kudashevs\AcceptLanguage\ValueObjects\QualityValue;

final class DefaultLanguage implements LanguageInterface
{
    private LanguageTag $tag;

    private QualityValue $quality;

    /**
     * @var array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool, allow_empty?: bool, fallback?: int|float}
     */
    private array $options;

    private bool $valid = false;

    /**
     * @param string $tag
     * @param int|float|string|null $quality
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool, allow_empty?: bool, fallback?: int|float} $options
     * @return DefaultLanguage
     */
    public static function create(string $tag, $quality, array $options = []): self
    {
        return new DefaultLanguage($tag, $quality, $options);
    }

    /**
     * @param string $tag
     * @param int|float|string|null $quality
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool, allow_empty?: bool, fallback?: int|float} $options
     * @return DefaultLanguage
     */
    public static function createInvalid(string $tag, $quality, array $options = []): self
    {
        $language = new DefaultLanguage($tag, $quality, $options);
        $language->valid = false;

        return $language;
    }

    /**
     * @param string $tag
     * @param int|float|string|null $quality
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool, allow_empty?: bool, fallback?: int|float} $options
     */
    private function __construct(string $tag, $quality, array $options = [])
    {
        $this->initOptions($options);
        $this->initLanguage($tag, $quality);
    }

    /**
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool, allow_empty?: bool, fallback?: int|float} $options
     */
    private function initOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param int|float|string|null $quality
     */
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
     * @return array{tag: LanguageTag, quality: QualityValue, valid: bool}
     */
    private function prepareLanguageState(string $tag, $quality): array
    {
        $languageTag = new LanguageTag($tag, $this->options);
        $qualityValue = new QualityValue($quality, $this->options);
        $valid = $this->isValidLanguage($languageTag, $qualityValue);

        return [
            'tag' => $languageTag,
            'quality' => $qualityValue,
            'valid' => $valid,
        ];
    }

    private function isValidLanguage(LanguageTag $tag, QualityValue $quality): bool
    {
        return $tag->isValid() && $quality->isValid();
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return array_merge(
            $this->tag->getOptions(),
            $this->quality->getOptions(),
        );
    }

    /**
     * @inheritDoc
     */
    public function getTag(): string
    {
        return $this->tag->getTag();
    }

    /**
     * @inheritDoc
     */
    public function getSubtags(): array
    {
        return $this->tag->getSubtags();
    }

    /**
     * @inheritDoc
     */
    public function getPrimarySubtag(): string
    {
        return $this->tag->getPrimarySubtag();
    }

    /**
     * @inheritDoc
     */
    public function getQuality()
    {
        return $this->quality->getQuality();
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
}
