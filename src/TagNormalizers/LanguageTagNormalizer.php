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

    /**
     * @param array $options
     */
    private function initOptions(array $options): void
    {
        $allowed = array_intersect_key($options, $this->options);

        $this->options = array_merge($this->options, $allowed);
    }

    /**
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string
    {
        return $this->normalizeLanguageTag($tag);
    }

    /**
     * Separates language tag, analyzes parts and normalizes them.
     *
     * @param $tag
     * @return string
     */
    private function normalizeLanguageTag($tag): string
    {
        $subtags = $this->splitLanguageTag($tag);

        return $this->normalizeTags($subtags);
    }

    /**
     * @param $tag
     * @return array
     */
    private function splitLanguageTag($tag): array
    {
        $harmonizedTag = str_replace('_', '-', $tag);

        return explode('-', $harmonizedTag);
    }

    private function normalizeTags(array $tags): string
    {
        if (count($tags) === 1) {
            return current($tags);
        }

        $normalizedTags = $this->normalizeSubtags($tags);

        return implode($this->options['separator'], $normalizedTags);
    }

    /**
     * @param array $subtags
     * @return array
     */
    private function normalizeSubtags(array $subtags): array
    {
        $normalized = [];

        foreach ($subtags as $index => $subtag) {
            if ($index === 0) {
                $normalized[] = $this->normalizePrimarySubtag($subtag);
                continue;
            }

            if ($this->isExtlang($subtag, $index)) {
                $this->processed++;

                if ($this->options['with_extlang']) {
                    $normalized[] = $this->normalizeExtlangSubtag($subtag);
                }
            }

            if ($this->isScript($subtag, $index)) {
                $this->processed++;

                if ($this->options['with_script']) {
                    $normalized[] = $this->normalizeScriptSubtag($subtag);
                }
            }

            if ($this->isRegion($subtag, $index)) {
                if ($this->options['with_region']) {
                    $normalized[] = $this->normalizeRegionSubtag($subtag);
                }
            }
        }

        return $normalized;
    }

    /**
     * @param string $subtag
     * @return string
     */
    private function normalizeExtlangSubtag(string $subtag): string
    {
        return strtolower($subtag);
    }

    /**
     * @param string $subtag
     * @return string
     */
    private function normalizePrimarySubtag(string $subtag): string
    {
        return strtolower($subtag);
    }

    /**
     * @param string $subtag
     * @return string
     */
    private function normalizeScriptSubtag(string $subtag): string
    {
        return ucfirst(strtolower($subtag));
    }

    /**
     * @param string $subtag
     * @return string
     */
    private function normalizeRegionSubtag(string $subtag): string
    {
        return strtoupper($subtag);
    }

    /**
     * @param string $value
     * @param int $position
     * @return bool
     */
    private function isExtlang(string $value, int $position): bool
    {
        return strlen($value) === self::EXTLANG_SUBTAG_LENGTH &&
            ($position === 1);
    }

    /**
     * @param string $value
     * @param int $position
     * @return bool
     */
    private function isScript(string $value, int $position): bool
    {
        return strlen($value) === self::SCRIPT_SUBTAG_LENGTH &&
            (
                $position === 1 ||
                ($this->processed === 1 && $position === 2)
            );
    }

    /**
     * @param string $value
     * @param int $position
     * @return bool
     */
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
