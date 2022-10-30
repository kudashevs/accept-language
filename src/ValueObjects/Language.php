<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

final class Language
{
    private string $tag;

    private $quality;

    private bool $valid = false;

    private function __construct(string $tag, $quality)
    {
        $this->initLanguage($tag, $quality);
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
     * @param int|float $quality
     * @return array{tag: string,quality: int|float,valid: bool}
     */
    private function prepareLanguageState(string $tag, $quality): array
    {
        if ($this->isValidTag(trim($tag)) && $this->isValidQuality($quality)) {
            return $this->generateValidLanguage($tag, $quality);
        }

        return $this->generateNonValidLanguage($tag, $quality);
    }

    private function isValidTag($value): bool
    {
        /**
         * A language tag is a sequence of one or more case-insensitive subtags, each separated by a hyphen character
         * ("-", %x2D). In most cases, a language tag consists of a primary language subtag that identifies a broad
         * family of related languages (e.g., "en" = English), which is optionally followed by a series of subtags that
         * refine or narrow that language's range (e.g., "en-CA" = the variety of English as communicated in Canada).
         * Whitespace is not allowed within a language tag. See RFC 7231, 3.1.3.1.
         */
        return $this->isWildcard($value) || $this->isValidLanguageTag($value);
    }

    private function isWildcard(string $header): bool
    {
        return $header === '*';
    }

    private function isValidLanguageTag(string $header): bool
    {
        return strlen($header) >= 2 && $this->isWhitespaceLess($header);
    }

    private function isWhitespaceLess(string $header): bool
    {
        return preg_match('/\s/', $header) === 0;
    }

    private function isValidQuality($value): bool
    {
        /**
         * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
         * and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
         */
        return $value >= 0 && $value <= 1;
    }

    private function generateValidLanguage(string $tag, $quality): array
    {
        return [
            'tag' => $tag,
            'quality' => $quality,
            'valid' => true,
        ];
    }

    private function generateNonValidLanguage(string $tag, $quality): array
    {
        $quality = $this->prepareQuality($quality);

        return [
            'tag' => $tag,
            'quality' => $quality,
            'valid' => false,
        ];
    }

    /**
     * @param int|float $quality
     * @return int|float
     */
    private function prepareQuality($quality)
    {
        if (is_int($quality) || is_float($quality)) {
            return $quality;
        }

        return (float)$quality;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return int|float
     */
    public function getQuality()
    {
        return $this->quality;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }
}
