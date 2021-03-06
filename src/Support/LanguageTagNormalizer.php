<?php

namespace Kudashevs\AcceptLanguage\Support;

final class LanguageTagNormalizer
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
        'with_extlang' => false,
        'with_script' => false,
        'with_region' => false,
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param string $tag
     * @param string $separator
     * @return string
     */
    public function normalize(string $tag, string $separator = '_'): string
    {
        return $this->normalizeLanguageTag($tag, $separator);
    }

    /**
     * Separates language tag, analyzes parts and normalizes them.
     *
     * @param $tag
     * @param string $separator
     * @return string
     */
    private function normalizeLanguageTag($tag, string $separator): string
    {
        $subtags = $this->splitLanguageTag($tag);

        if (count($subtags) > 1) {
            $subtags = $this->normalizeSubtags($subtags);
        }

        return implode($separator, $subtags);
    }

    /**
     * @param $rawTag
     * @return array
     */
    private function splitLanguageTag($rawTag): array
    {
        return explode('-', str_replace('_', '-', $rawTag));
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
                continue;
            }

            if ($this->isScript($subtag, $index)) {
                $this->processed++;
                $normalized[] = $this->normalizeScriptSubtag($subtag);
            }

            if ($this->isRegion($subtag, $index)) {
                $normalized[] = $this->normalizeRegionSubtag($subtag);
            }
        }

        return $normalized;
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
