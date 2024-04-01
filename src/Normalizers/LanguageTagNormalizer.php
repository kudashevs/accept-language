<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageTagNormalizer implements TagNormalizerInterface
{
    /**
     * 'separator' A string with a custom separator to use in a normalized tag.
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
     * @param array<string, bool|string> $options
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
    }

    private function initOptions(array $options): void
    {
        $allowed = array_intersect_key($options, $this->options);

        $this->options = array_merge($this->options, $allowed);
    }

    /**
     * Return a normalized language tag. The normalization process includes:
     * - replacing a separator (a hyphen character) with a value from the "separator" option
     * - omitting unwanted subtags according to the pre-selected options
     * - formatting subtags according to RFC 5646 and RFC 4647
     *
     * If the provided tag is not valid, it is returned unchanged.
     *
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string
    {
        // A language tag is composed from a sequence of one or more "subtags". See RFC 5646, Section 2.2.1.
        $subtags = $this->extractSubtags($tag);

        if ($this->isValidSetOfSubtags($subtags)) {
            return $this->generateNormalizedTagFromSubtags($subtags);
        }

        return $tag;
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

        return $this->retrieveAppropriateSubtags($subtags);
    }

    /**
     * @return array<string, string>
     */
    private function retrieveAppropriateSubtags(array $subtags): array
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
    private function isValidSetOfSubtags(array $subtags): bool
    {
        return count($subtags) === 4;
    }

    private function generateNormalizedTagFromSubtags(array $subtags): string
    {
        $normalizedSubtags = [
            $this->normalizePrimary($subtags['primary']),
            $this->options['with_extlang'] ? $this->normalizeExtlang($subtags['extlang']) : '',
            $this->options['with_script'] ? $this->normalizeScript($subtags['script']) : '',
            $this->options['with_region'] ? $this->normalizeRegion($subtags['region']) : '',
        ];

        return implode($this->options['separator'], array_filter($normalizedSubtags, 'strlen'));
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
