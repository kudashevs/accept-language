<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

use Kudashevs\AcceptLanguage\Normalizers\AbstractTagNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\LanguageTagNormalizer;

final class LanguageTag
{
    private const MINIMUM_PRIMARY_SUBTAG_LENGTH = 2;

    private const MAXIMUM_PRIMARY_SUBTAG_LENGTH = 3;

    private const DEFAULT_SEPARATOR = '-';

    private AbstractTagNormalizer $normalizer;

    private string $separator;

    private string $tag;

    private bool $valid = true;

    /**
     * @param string $tag
     * @param array<string, bool|string> $options
     */
    public function __construct(string $tag, array $options = [])
    {
        $this->initNormalizer($options);
        $this->initSeparator($options);

        $this->initTag($tag);
    }

    private function initNormalizer(array $options): void
    {
        $this->normalizer = $this->createTagNormalizer($options);
    }

    private function createTagNormalizer(array $options): AbstractTagNormalizer
    {
        return new LanguageTagNormalizer($options);
    }

    private function initSeparator(array $options): void
    {
        $this->separator = $options['separator'] ?? self::DEFAULT_SEPARATOR;
    }

    private function initTag(string $tag): void
    {
        if (!$this->isValidTag($tag)) {
            $this->tag = $this->prepareSafe($tag);
            $this->valid = false;

            return;
        }

        $this->tag = $this->normalizeTag($tag);
    }

    private function isValidTag($tag): bool
    {
        return $this->isWildcard($tag) || $this->isValidLanguageTag($tag);
    }

    private function isWildcard(string $tag): bool
    {
        return $tag === '*';
    }

    private function isValidLanguageTag(string $tag): bool
    {
        /**
         * A language tag is a sequence of one or more case-insensitive subtags, each separated by a hyphen character
         * ("-", %x2D). In most cases, a language tag consists of a primary language subtag that identifies a broad
         * family of related languages (e.g., "en" = English), which is optionally followed by a series of subtags that
         * refine or narrow that language's range (e.g., "en-CA" = the variety of English as communicated in Canada).
         * Whitespace is not allowed within a language tag. See RFC 7231, Section 3.1.3.1.
         */
        return $this->isValidLength($tag) && $this->isValidCharacterRange($tag) && $this->isLikeLanguageTag($tag);
    }

    private function isValidLength(string $tag): bool
    {
        return strlen($tag) >= self::MINIMUM_PRIMARY_SUBTAG_LENGTH;
    }

    private function isValidCharacterRange(string $tag): bool
    {
        return preg_match('/[a-z0-9\-]/i', $tag) === 1;
    }

    private function isLikeLanguageTag($tag)
    {
        return $this->isPrimarySubtag($tag) || $this->isPrimaryWithSubtags($tag);
    }

    private function isPrimarySubtag(string $tag): bool
    {
        /**
         * The primary language subtag is the first subtag in a language tag. See RFC 5646, Section 2.2.1.
         */
        return strlen($tag) >= self::MINIMUM_PRIMARY_SUBTAG_LENGTH &&
            strlen($tag) <= self::MAXIMUM_PRIMARY_SUBTAG_LENGTH &&
            $this->isSeparatorLess($tag);
    }

    private function isPrimaryWithSubtags(string $tag): bool
    {
        return strlen($tag) > self::MAXIMUM_PRIMARY_SUBTAG_LENGTH && !$this->isSeparatorLess($tag);
    }

    private function isSeparatorLess(string $tag): bool
    {
        return strpos($tag, '-') === false;
    }

    private function prepareSafe(string $tag): string
    {
        return htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
    }

    private function normalizeTag(string $tag): string
    {
        return $this->normalizer->normalize($tag);
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return array
     */
    public function getSubtags(): array
    {
        return explode($this->separator, $this->tag);
    }

    /**
     * @return string
     */
    public function getPrimarySubtag(): string
    {
        return current($this->getSubtags());
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
}
