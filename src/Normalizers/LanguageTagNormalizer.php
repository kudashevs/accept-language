<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageTagNormalizer implements TagNormalizerInterface
{
    private const SUBTAG_NORMALIZERS = [
        'primary' => 'normalizePrimary',
        'extlang' => 'normalizeExtlang',
        'script' => 'normalizeScript',
        'region' => 'normalizeRegion',
    ];

    private const OPTION_PREFIX = 'with_';

    public function __construct()
    {
    }

    /**
     * Return a normalized language tag. The normalization process includes:
     * - omitting unwanted subtags according to the pre-selected options
     * - formatting subtags according to RFC 5646 and RFC 4647
     *
     * If the provided tag is not valid, it is returned unchanged.
     *
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag, array $options = []): string
    {
        // A language tag is composed from a sequence of one or more "subtags". See RFC 5646, Section 2.2.1.
        $subtags = $this->extractSubtags($tag);

        if ($this->isInvalidSetOfSubtags($subtags)) {
            return $tag;
        }

        return $this->generateNormalizedTagFromSubtags($subtags, $options);
    }

    /**
     * @return array<string, string>
     */
    private function extractSubtags(string $tag): array
    {
        preg_match(
            '/^
                        (?<primary>[a-z]{1,8})
                        (-(?<extlang>[a-z]{3})?)?
                        (-(?<script>[a-z]{4})?)?
                        (-(?<region>[a-z]{2}|[0-9]{3})?)?
                        (-([a-z][a-z0-9]{4,}|[0-9][a-z0-9]{3,})?)?      # variant subtag
                        (-([a-z]-[a-z-]+)?)?                            # extension subtag
                        (-(x-[[a-z-]+)?)?                               # private subtag
                    $/iSx',
            $tag,
            $subtags,
            PREG_UNMATCHED_AS_NULL,
        );

        return $this->retrieveFoundSubtags($subtags);
    }

    /**
     * @return array<string, string>
     */
    private function retrieveFoundSubtags(array $subtags): array
    {
        $groups = $this->collectNamedGroups($subtags);

        return array_map('strval', $groups);
    }

    private function collectNamedGroups(array $subtags): array
    {
        return array_filter($subtags, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<string, string> $subtags
     */
    private function isInvalidSetOfSubtags(array $subtags): bool
    {
        return count($subtags) !== 4;
    }

    /**
     * @param array<string, string> $subtags
     * @param array<string, bool> $options
     */
    private function generateNormalizedTagFromSubtags(array $subtags, array $options): string
    {
        $applicableSubtags = $this->retrieveApplicableSubtags($subtags, $options);

        $normalizedSubtags = $this->normalizeSubtags($applicableSubtags);

        // Subtags are distinguished and separated from one another by a hyphen.
        // For more information about a separator see RFC 5646, Section 2.1.
        return implode(
            '-',
            $normalizedSubtags
        );
    }

    private function retrieveApplicableSubtags(array $subtags, array $options): array
    {
        return array_filter($subtags, function ($value, $key) use ($options) {
            return !$this->isEmpty($value) && ($this->isPrimary($key) || $this->isRequired($key, $options));
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function isEmpty(string $value): bool
    {
        return trim($value) === '';
    }

    private function isPrimary(string $subtag): bool
    {
        return $subtag === 'primary';
    }

    private function isRequired(string $key, array $options): bool
    {
        $optionKey = self::OPTION_PREFIX . $key;

        return isset($options[$optionKey]) && $options[$optionKey] === true;
    }

    private function normalizeSubtags(array $subtags): array
    {
        $normalizedSubtags = [];

        foreach ($subtags as $title => $value) {
            $normalizedSubtags[$title] = [$this, self::SUBTAG_NORMALIZERS[$title]]($value);
        }

        return $normalizedSubtags;
    }

    private function normalizePrimary(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function normalizeExtlang(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function normalizeScript(string $subtag): string
    {
        return ucfirst(strtolower($subtag));
    }

    private function normalizeRegion(string $subtag): string
    {
        return strtoupper($subtag);
    }
}
