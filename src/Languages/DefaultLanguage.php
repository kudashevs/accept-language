<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Languages;

use Kudashevs\AcceptLanguage\ValueObjects\LanguageTag;
use Kudashevs\AcceptLanguage\ValueObjects\QualityValue;

final class DefaultLanguage implements LanguageInterface, ConfigurableInterface
{
    private array $options;

    private LanguageTag $tag;

    private QualityValue $quality;

    private bool $valid = false;

    /**
     * @param string $tag
     * @param int|float|string|null $quality
     * @param array<string, bool|string> $options
     * @return DefaultLanguage
     */
    public static function create(string $tag, $quality, array $options = []): self
    {
        return new DefaultLanguage($tag, $quality, $options);
    }

    /**
     * @param string $tag
     * @param mixed $quality
     * @param array<string, bool|string> $options
     * @return DefaultLanguage
     */
    public static function createInvalid(string $tag, $quality, array $options = []): self
    {
        $language = new DefaultLanguage($tag, $quality, $options);
        $language->valid = false;

        return $language;
    }

    private function __construct(string $tag, $quality, array $options = [])
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
     * {@inheritDoc}
     *
     * @return array<string, bool|string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag->getTag();
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, string>
     */
    public function getSubtags(): array
    {
        return $this->tag->getSubtags();
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getPrimarySubtag(): string
    {
        return $this->tag->getPrimarySubtag();
    }

    /**
     * {@inheritDoc}
     *
     * @return int|float
     */
    public function getQuality()
    {
        return $this->quality->getQuality();
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
}
