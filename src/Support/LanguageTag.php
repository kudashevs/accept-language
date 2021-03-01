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
        $parts = $this->splitLanguageTag($tag);

        if (count($parts) > 1) {
            $parts = $this->normalizeLanguageTagParts($parts);
        }

        return implode('-', $parts);
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
     * @param array $parts
     * @return array
     */
    private function normalizeLanguageTagParts(array $parts): array
    {
        $normalized = [];

        foreach ($parts as $index => $part) {
            if ($index === 0) {
                $normalized[] = $part;
                continue;
            }

            if ($this->isExtlang($part, $index)) {
                $this->processed++;
                continue;
            }

            if ($this->isScript($part, $index)) {
                $this->processed++;
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
