<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

use Kudashevs\AcceptLanguage\Normalizers\LanguageTagNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\TagNormalizerInterface;

final class LanguageTag
{
    // The language range starts from the 1*8ALPHA notation which means that
    // the minimum length is equal to 1 and the maximum length is equal to 8.
    // For more information about language ranges see RFC 4647, Section 2.2.
    private const MINIMUM_PRIMARY_SUBTAG_LENGTH = 1;
    private const MAXIMUM_PRIMARY_SUBTAG_LENGTH = 8;

    private const DEFAULT_SEPARATOR = '-';

    private TagNormalizerInterface $normalizer;

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

    private function createTagNormalizer(array $options): TagNormalizerInterface
    {
        return new LanguageTagNormalizer($options);
    }

    private function initSeparator(array $options): void
    {
        $this->separator = $options['separator'] ?? self::DEFAULT_SEPARATOR;
    }

    private function initTag(string $tag): void
    {
        $preparedTag = $this->prepareTag($tag);

        if (!$this->isValidTag($preparedTag)) {
            $this->tag = $this->prepareSafe($tag);
            $this->valid = false;

            return;
        }

        $this->tag = $this->normalizeTag($preparedTag);
    }

    private function prepareTag(string $tag): string
    {
        return str_replace($this->separator, '-', $tag);
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
        // A language tag is a sequence of one or more case-insensitive subtags, each separated by a hyphen character
        // ("-", %x2D). In most cases, a language tag consists of a primary language subtag that identifies a broad
        // family of related languages (e.g., "en" = English), which is optionally followed by a series of subtags that
        // refine or narrow that language's range (e.g., "en-CA" = the variety of English as communicated in Canada).
        // Whitespace is not allowed within a language tag. See RFC 7231, Section 3.1.3.1.
        return $this->isValidLength($tag) && $this->isValidCharacterRange($tag) && $this->isLikeLanguageTag($tag);
    }

    private function isValidLength(string $tag): bool
    {
        return strlen($tag) >= self::MINIMUM_PRIMARY_SUBTAG_LENGTH;
    }

    private function isValidCharacterRange(string $tag): bool
    {
        return preg_match('/^[a-z0-9\-]+$/iSU', $tag) === 1;
    }

    private function isLikeLanguageTag($tag)
    {
        return $this->isPrimarySubtag($tag) || $this->isPrimaryWithSubtags($tag);
    }

    private function isPrimarySubtag(string $tag): bool
    {
        // The primary language subtag is the first subtag in a language tag. See RFC 5646, Section 2.2.1.
        return strlen($tag) >= self::MINIMUM_PRIMARY_SUBTAG_LENGTH
            && strlen($tag) <= self::MAXIMUM_PRIMARY_SUBTAG_LENGTH
            && $this->isAlphaCharacterRange($tag)
            && $this->isSeparatorLess($tag);
    }

    private function isAlphaCharacterRange(string $tag): bool
    {
        return preg_match('/^[a-z]+$/iSU', $tag) === 1;
    }

    private function isPrimaryWithSubtags(string $tag): bool
    {
        $subtags = explode('-', $tag);

        return count($subtags) > 1 && $this->isPrimarySubtag($subtags[0]);
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
