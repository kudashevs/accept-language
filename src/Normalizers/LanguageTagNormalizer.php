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
        'with_extlang' => false,
        'with_script' => true,
        'with_region' => true,
    ];

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
        $options = array_merge($this->options, $options);

        // A language tag is composed from a sequence of one or more "subtags". See RFC 5646, Section 2.2.1.
        $subtags = $this->extractSubtags($tag);

        if ($this->isInvalidSetOfSubtags($subtags)) {
            return $tag;
        }

        return $this->generateNormalizedTagFromSubtags($subtags, $options);
    }

    /**
     * @param array<string, string|bool> $options
     */
    private function applyOptions(array $options): void
    {
        $allowed = array_intersect_key($options, $this->options);

        $this->options = array_merge($this->options, $allowed);
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
        $normalizedSubtags = [
            $this->normalizePrimary($subtags['primary']),
            isset($options['with_extlang']) && $options['with_extlang'] === true ? $this->normalizeExtlang($subtags['extlang']) : '',
            isset($options['with_script']) && $options['with_script'] === true ? $this->normalizeScript($subtags['script']) : '',
            isset($options['with_region']) && $options['with_region'] === true ? $this->normalizeRegion($subtags['region']) : '',
        ];

        // Subtags are distinguished and separated from one another by a hyphen.
        // For more information about a separator see RFC 5646, Section 2.1.
        return implode(
            '-',
            array_filter($normalizedSubtags, 'strlen')
        );
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
