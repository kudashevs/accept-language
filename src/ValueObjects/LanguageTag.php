<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\ValueObjects;

use Kudashevs\AcceptLanguage\Normalizers\LanguageTagNormalizer;
use Kudashevs\AcceptLanguage\Normalizers\TagNormalizerInterface;

final class LanguageTag
{
    /*
     * The language range starts with the 1*8ALPHA notation which means that
     * the minimum length is equal to 1 and the maximum length is equal to 8.
     * For more information about language ranges see RFC 4647, Section 2.2.
     */
    private const MINIMUM_PRIMARY_SUBTAG_LENGTH = 1;
    private const MAXIMUM_PRIMARY_SUBTAG_LENGTH = 8;

    /*
     * The separators that will be used to distinguish subtags.
     */
    private const SUPPORTED_SEPARATORS = ['_', '-'];

    private TagNormalizerInterface $normalizer;

    private string $tag;

    private bool $valid = true;

    /**
     * 'separator' A string with a custom separator to use in the language tag.
     * 'with_extlang' A boolean that defines whether to add an extlang subtag to a normalized tag.
     * 'with_script' A boolean that defines whether to add a script subtag to a normalized tag.
     * 'with_region' A boolean that defines whether to add a region subtag to a normalized tag.
     *
     * @var array{separator: string, with_extlang: bool, with_script: bool, with_region: bool}
     */
    private array $options = [
        'separator' => '-',
        'with_extlang' => false,
        'with_script' => true,
        'with_region' => true,
    ];

    /**
     * @param string $tag
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool} $options
     */
    public function __construct(string $tag, array $options = [])
    {
        $this->initNormalizer();
        $this->initOptions($options);

        $this->initTag($tag);
    }

    private function initNormalizer(): void
    {
        $this->normalizer = $this->createDefaultNormalizer();
    }

    private function createDefaultNormalizer(): TagNormalizerInterface
    {
        return new LanguageTagNormalizer();
    }

    /**
     * @param array{separator?: string, with_extlang?: bool, with_script?: bool, with_region?: bool} $options
     */
    private function initOptions(array $options): void
    {
        $allowed = array_intersect_key($options, $this->options);

        $this->options = array_merge($this->options, $allowed);
    }

    private function initTag(string $tag): void
    {
        /*
         * To perform a normalization process, the tag value should conform to the standard.
         */
        $preparedTag = $this->prepareTag($tag);

        if (!$this->isValidTag($preparedTag)) {
            $this->tag = $this->prepareSafe($tag);
            $this->valid = false;

            return;
        }

        $this->tag = $this->normalizeTag($preparedTag);
    }

    /**
     * Return a prepared language tag. The preparation process includes:
     * - replacing all separators (including the provided one) with the standard separator
     */
    private function prepareTag(string $tag): string
    {
        /*
         * Subtags are distinguished and separated from one another by a hyphen.
         * For more information about a separator see RFC 5646, Section 2.1.
        */
        return str_replace($this->retrieveSeparators(), '-', $tag);
    }

    /**
     * @return array<array-key, string>
     */
    private function retrieveSeparators(): array
    {
        return array_merge([$this->options['separator']], self::SUPPORTED_SEPARATORS);
    }

    private function isValidTag(string $tag): bool
    {
        return $this->isWildcard($tag) || $this->isValidLanguageTag($tag);
    }

    private function isWildcard(string $tag): bool
    {
        return $tag === '*';
    }

    private function isValidLanguageTag(string $tag): bool
    {
        /*
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
        return preg_match('/^[a-z0-9\-]+$/iSU', $tag) === 1;
    }

    private function isLikeLanguageTag(string $tag): bool
    {
        return $this->isPrimarySubtag($tag) || $this->isPrimaryWithSubtags($tag);
    }

    private function isPrimarySubtag(string $tag): bool
    {
        /*
         * The primary language subtag is the first subtag in a language tag. See RFC 5646, Section 2.2.1.
         */
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
        return $this->normalizer->normalize($tag, $this->options);
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return str_replace('-', $this->options['separator'], $this->tag);
    }

    /**
     * @return array<array-key, string>
     */
    public function getSubtags(): array
    {
        return explode('-', $this->tag);
    }

    /**
     * @return string
     */
    public function getPrimarySubtag(): string
    {
        return array_values($this->getSubtags())[0];
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return array{separator: string, with_extlang: bool, with_script: bool, with_region: bool}
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
