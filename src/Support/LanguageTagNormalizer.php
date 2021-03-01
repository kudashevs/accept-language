<?php

namespace Kudashevs\AcceptLanguage\Support;

class LanguageTag
{
    /**
     * @var int
     */
    private $processed = 0;

    public function __construct()
    {
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

        if (count($subtags) > 1) {
            $subtags = $this->normalizeSubtags($subtags);
        }

        return implode('-', $subtags);
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
        return (strlen($value) === 3 && ($position === 1));
    }

    /**
     * @param string $value
     * @param int $position
     * @return bool
     */
    private function isScript(string $value, int $position): bool
    {
        return strlen($value) === 4 &&
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
        return strlen($value) === 2 &&
            (
                $position === 1 ||
                ($this->processed === 1 && $position === 2) ||
                ($this->processed === 2 && $position === 3)
            );
    }
}
