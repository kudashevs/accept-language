<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

final class LanguageTag
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
        /**
         * A language tag is a sequence of one or more case-insensitive subtags, each separated by a hyphen character
         * ("-", %x2D). In most cases, a language tag consists of a primary language subtag that identifies a broad
         * family of related languages (e.g., "en" = English), which is optionally followed by a series of subtags that
         * refine or narrow that language's range (e.g., "en-CA" = the variety of English as communicated in Canada).
         * Whitespace is not allowed within a language tag. See RFC 7231, 3.1.3.1.
         */
        return $this->isWildcard($value) || strlen($value) >= 2;
    }

    private function isWildcard(string $header): bool
    {
        return $header === '*';
    }

    private function isValidQuality($value): bool
    {
        /**
         * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
         * and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
         */
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
