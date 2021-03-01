<?php

namespace Kudashevs\AcceptLanguage\Support;

class LanguageTag
{
    /**
     * @var bool
     */
    private $hasExtlang = 0;

    /**
     * @var bool
     */
    private $hasScript = 0;

    public function __construct()
    {
    }

    /**
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string
    {
        $normalizedLanguageTag = $this->normalizeLanguageTag($tag);

        return $normalizedLanguageTag;
    }

    /**
     * Separates language tag, analyzes parts and normalizes them.
     *
     * @param $tag
     * @return string
     */
    private function normalizeLanguageTag($tag): string
    {
        $parts = $this->separateLanguageTag($tag);

        if (count($parts) > 1) {
            $parts = $this->normalizeLanguageTagParts($parts);
        }

        return implode('-', $parts);
    }

    /**
     * @param $rawTag
     * @return array
     */
    private function separateLanguageTag($rawTag): array
    {
        return explode('-', str_replace('_', '-', $rawTag));
    }

    /**
     * @param array $parts
     * @return array
     */
    private function normalizeLanguageTagParts(array $parts): array
    {
        foreach ($parts as $index => $part) {
            if ($index === 0) {
                $normalized[] = $part;
                continue;
            }

            if ($this->isExtlang($part, $index)) {
                $this->hasExtlang++;
                continue;
            }

            if ($this->isScript($part, $index)) {
                $this->hasScript++;
                $normalized[] = $part;
            }

            if ($this->isRegion($part, $index)) {
                $normalized[] = $part;
            }
        }

        return $normalized;
    }

    /**
     * @param string $value
     * @param int $position
     * @return bool
     */
    private function isExtlang(string $value, int $position): bool
    {
        return (3 === strlen($value) && ($position === 1));
    }

    /**
     * @param string $value
     * @param int $position
     * @return bool
     */
    private function isScript(string $value, int $position): bool
    {
        return (
            strlen($value) === 4 &&
            (
                $position === 1 ||
                ($this->hasExtlang && $position === 2)
            )
        );
    }

    /**
     * @param string $value
     * @param int $position
     * @return bool
     */
    private function isRegion(string $value, int $position): bool
    {
        return (
            strlen($value) === 2 &&
            (
                $position === 1 ||
                ($this->hasExtlang && $position === 2) ||
                ($this->hasScript && $position === 2) ||
                ($this->hasExtlang && $this->hasScript && $position === 3)
            )
        );
    }
}
