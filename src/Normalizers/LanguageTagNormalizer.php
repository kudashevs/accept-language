<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageTagNormalizer implements AbstractTagNormalizer
{
    private array $options = [
        'separator' => '-',
        'with_extlang' => false,
        'with_script' => true,
        'with_region' => true,
    ];

    /**
     * @param array $options
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
     * - replacing a separator (a hyphen character) with a value of the "separator" option
     * - omitting unwanted subtags according to the pre-selected options
     * - formatting subtags according to RFC 4646 and RFC 4647
     *
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string
    {
        $subtags = $this->extractSubtags($tag);

        return $this->generateNormalizedTag($tag, $subtags);
    }

    /**
     * @return array<string, string|null>
     */
    private function extractSubtags($tag): array
    {
        preg_match(
            '/^(?<primary>[a-z]{2,3})(-(?<extlang>[a-z]{3})?)?(-(?<script>[a-z]{4})?)?(-(?<region>[a-z]{2}|[0-9]{3})?)?$/i',
            $tag,
            $subtags,
            PREG_UNMATCHED_AS_NULL,
        );

        return array_filter($subtags, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    private function generateNormalizedTag(string $tag, array $subtags): string
    {
        if ($this->isUnrecognizableTag($subtags)) {
            return $tag;
        }

        return $this->generateNormalizedTagFromSubtags($subtags);
    }

    private function isUnrecognizableTag(array $subtags): bool
    {
        return count($subtags) === 0;
    }

    private function generateNormalizedTagFromSubtags(array $subtags): string
    {
        $primary = $subtags['primary'] ?? '';
        $extlang = $subtags['extlang'] ?? '';
        $script = $subtags['script'] ?? '';
        $region = $subtags['region'] ?? '';

        $normalizedSubtags = [
            'primary' => $this->preparePrimary($primary),
            'extlang' => $this->prepareExtlang($extlang),
            'script' => $this->prepareScript($script),
            'region' => $this->prepareRegion($region),
        ];

        return implode($this->options['separator'], array_filter($normalizedSubtags, 'strlen'));
    }

    private function preparePrimary($value): string
    {
        return $this->normalizePrimary($value);
    }

    private function normalizePrimary(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function prepareExtlang($value): string
    {
        if ($this->options['with_extlang'] === true) {
            return $this->normalizeExtlang($value);
        }

        return '';
    }

    private function normalizeExtlang(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function prepareScript($value): string
    {
        if ($this->options['with_script'] === true) {
            return $this->normalizeScript($value);
        }

        return '';
    }

    private function normalizeScript(string $subtag): string
    {
        return ucfirst(strtolower($subtag));
    }

    private function prepareRegion($value): string
    {
        if ($this->options['with_region'] === true) {
            return $this->normalizeRegion($value);
        }

        return '';
    }

    private function normalizeRegion(string $subtag): string
    {
        return strtoupper($subtag);
    }
}
