<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LanguageTags;

final class LanguageTag
{
    private string $languageTag;

    private float $quality;

    public function __construct(string $tag, $quality)
    {
        $this->initLanguageTag($tag, $quality);
    }

    private function initLanguageTag(string $tag, $quality): void
    {
        $this->languageTag = $tag;
        $this->quality = $quality;
    }

    public function getLanguageTag(): string
    {
        return $this->languageTag;
    }

    public function getQuality(): float
    {
        return $this->quality;
    }
}
