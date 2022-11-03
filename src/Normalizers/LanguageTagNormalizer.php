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

        $normalizedSubtags = $this->normalizeSubtags($subtags);

        return implode($this->options['separator'], $normalizedSubtags);
    }

    private function isUnrecognizableTag(array $subtags): bool
    {
        return count($subtags) === 0;
    }

    private function normalizeSubtags(array $subtags): array
    {
        $normalizedSubtags = [
            'primary' => $this->preparePrimary($subtags['primary']),
            'extlang' => $this->prepareExtlang($subtags['extlang']),
            'script' => $this->prepareScript($subtags['script']),
            'region' => $this->prepareRegion($subtags['region']),
        ];

        return array_filter($normalizedSubtags, function ($subtag) {
            return $subtag !== '';
        });
    }

    private function preparePrimary($value): string
    {
        if ($this->isAppropriateSubtag($value)) {
            return $this->normalizePrimary($value);
        }

        return '';
    }

    private function isAppropriateSubtag($value): bool
    {
        return is_string($value) && $value !== '';
    }

    private function normalizePrimary(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function prepareExtlang($value): string
    {
        if ($this->options['with_extlang'] === true && $this->isAppropriateSubtag($value)) {
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
        if ($this->options['with_script'] === true && $this->isAppropriateSubtag($value)) {
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
        if ($this->options['with_region'] === true && $this->isAppropriateSubtag($value)) {
            return $this->normalizeRegion($value);
        }

        return '';
    }

    private function normalizeRegion(string $subtag): string
    {
        return strtoupper($subtag);
    }
}
