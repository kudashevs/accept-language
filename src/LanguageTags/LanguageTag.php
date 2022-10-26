<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LanguageTags;

final class LanguageTag implements AbstractTag
{
    private string $languageTag;

    private float $quality;

    public function __construct(string $tag, float $quality)
    {
        $this->initTag($tag, $quality);
    }

    private function initTag(string $tag, $quality): void
    {
        [$this->languageTag, $this->quality] = $this->prepareTagWithQuality($tag, $quality);
    }

    /**
     * @return array<string,int|float>
     */
    private function prepareTagWithQuality(string $tag, $quality): array
    {
        if ($this->isValidLanguageTag(trim($tag)) && $this->isValidQuality($quality)) {
            return $this->generateLanguageTag($tag, $quality);
        }

        return $this->generateEmptyLanguageTag();
    }

    private function isValidLanguageTag($value): bool
    {
        return $this->isWildcard($value) || strlen($value) >= 2;
    }

    private function isWildcard(string $header): bool
    {
        return $header === '*';
    }

    private function isValidQuality($value): bool
    {
        return $value > 0 && max(min($value, 1), 0.001) === $value;
    }

    private function generateLanguageTag(string $tag, float $quality): array
    {
        return [$tag, $quality]; // @todo add keys
    }

    private function generateEmptyLanguageTag(): array
    {
        return ['', 0]; // @todo add keys
    }

    public function getTag(): string
    {
        return $this->languageTag;
    }

    public function getQuality(): float
    {
        return $this->quality;
    }
}
