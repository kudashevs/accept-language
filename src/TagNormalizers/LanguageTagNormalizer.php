<?php

namespace Kudashevs\AcceptLanguage\TagNormalizers;

final class LanguageTagNormalizer implements TagNormalizer
{
    private const EXTLANG_SUBTAG_LENGTH = 3;
    private const SCRIPT_SUBTAG_LENGTH = 4;
    private const REGION_SUBTAG_LENGTH = 2;

    /**
     * @var int
     */
    private $processed = 0;

    /**
     * @var array
     */
    private $options = [
        'separator' => '-',
        'with_extlang' => false,
        'with_script' => true,
        'with_region' => true,
    ];

    /**
     * LanguageTagNormalizer constructor.
     *
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
     * Return a normalized language tag.
     * ???
     *
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string
    {
        $subtags = $this->splitLanguageTag($tag);

        return $this->generateNormalizedTag($subtags);
    }

    private function splitLanguageTag($tag): array
    {
        $harmonizedTag = str_replace('_', '-', $tag);

        return explode('-', $harmonizedTag);
    }

    private function generateNormalizedTag(array $subtags): string
    {
        if (count($subtags) === 1) {
            return current($subtags);
        }

        $normalizedSubtags = $this->normalizeSubtags($subtags);

        return implode($this->options['separator'], $normalizedSubtags);
    }

    private function normalizeSubtags(array $subtags): array
    {
        $normalizedSubtags = [];

        foreach ($subtags as $index => $subtag) {
            if ($this->isPrimaryTag($index)) {
                $normalizedSubtags[] = $this->normalizePrimary($subtag);
                continue;
            }

            if ($this->isExtlang($subtag, $index)) {
                $this->processed++;

                if ($this->options['with_extlang']) {
                    $normalizedSubtags[] = $this->normalizeExtlang($subtag);
                }
            }

            if ($this->isScript($subtag, $index)) {
                $this->processed++;

                if ($this->options['with_script']) {
                    $normalizedSubtags[] = $this->normalizeScriptSubtag($subtag);
                }
            }

            if ($this->isRegion($subtag, $index)) {
                if ($this->options['with_region']) {
                    $normalizedSubtags[] = $this->normalizeRegionSubtag($subtag);
                }
            }
        }

        return $normalizedSubtags;
    }

    private function normalizeExtlang(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function normalizePrimary(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function normalizeScriptSubtag(string $subtag): string
    {
        return ucfirst(strtolower($subtag));
    }

    private function normalizeRegionSubtag(string $subtag): string
    {
        return strtoupper($subtag);
    }

    private function isPrimaryTag($index): bool
    {
        return $index === 0;
    }

    private function isExtlang(string $value, int $position): bool
    {
        return strlen($value) === self::EXTLANG_SUBTAG_LENGTH &&
            ($position === 1);
    }

    private function isScript(string $value, int $position): bool
    {
        return strlen($value) === self::SCRIPT_SUBTAG_LENGTH &&
            (
                $position === 1 ||
                ($this->processed === 1 && $position === 2)
            );
    }

    private function isRegion(string $value, int $position): bool
    {
        return strlen($value) === self::REGION_SUBTAG_LENGTH &&
            (
                $position === 1 ||
                ($this->processed === 1 && $position === 2) ||
                ($this->processed === 2 && $position === 3)
            );
    }
}
