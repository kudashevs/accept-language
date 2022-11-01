<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLanguageTagArgumentException;
use Kudashevs\AcceptLanguage\Normalizers\AbstractTagNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\LanguageTagNormalizer;

final class LanguageTag
{
    private const DEFAULT_SEPARATOR = '-';

    private AbstractTagNormalizer $normalizer;

    private string $separator;

    private string $tag;

    private bool $valid = true;

    /**
     * @param string $tag
     * @param array $options
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
            throw new InvalidLanguageTagArgumentException(
                sprintf('The language tag "%s" is invalid.', $tag)
            );
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
        return $this->isLongEnough($tag) && $this->isWhitespaceLess($tag) && $this->isLikeLanguageTag($tag);
    }

    private function isLongEnough(string $tag): bool
    {
        return strlen($tag) >= 2;
    }

    private function isWhitespaceLess(string $tag): bool
    {
        return preg_match('/\s/', $tag) === 0;
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
        return strlen($tag) >= 2 &&
            strlen($tag) <= 3 &&
            $this->isSeparatorLess($tag);
    }

    private function isPrimaryWithSubtags(string $tag): bool
    {
        return strlen($tag) > 3 && !$this->isSeparatorLess($tag);
    }

    private function isSeparatorLess(string $tag): bool
    {
        return strpos($tag, '-') === false;
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
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
}
