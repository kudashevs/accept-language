<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageTagNormalizer implements AbstractTagNormalizer
{
    private const SUBTAG_SEPARATOR = '-';
    private const EXTLANG_SUBTAG_LENGTH = 3;
    private const SCRIPT_SUBTAG_LENGTH = 4;
    private const REGION_SUBTAG_LENGTH = 2;

    private int $processed = 0;

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
        $subtags = $this->splitLanguageTag($tag);

        return $this->generateNormalizedTag($subtags);
    }

    private function splitLanguageTag($tag): array
    {
        return explode(self::SUBTAG_SEPARATOR, $tag);
    }

    private function generateNormalizedTag(array $subtags): string
    {
        $normalizedSubtags = $this->normalizeSubtags($subtags);

        return implode($this->options['separator'], $normalizedSubtags);
    }

    private function normalizeSubtags(array $subtags): array
    {
        $normalizedSubtags = [];

        foreach ($subtags as $index => $subtag) {
            if ($this->isPrimary($index)) {
                $normalizedSubtags[] = $this->normalizePrimary($subtag);
                continue;
            }

            if ($this->isExtlang($subtag, $index)) {
                $normalizedSubtags[] = $this->normalizeExtlang($subtag);
            }

            if ($this->isScript($subtag, $index)) {
                $normalizedSubtags[] = $this->normalizeScript($subtag);
            }

            if ($this->isRegion($subtag, $index)) {
                $normalizedSubtags[] = $this->normalizeRegion($subtag);
            }

            $this->processed++;
        }

        return $normalizedSubtags;
    }

    private function isPrimary($index): bool
    {
        return $index === 0;
    }

    private function normalizePrimary(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function isExtlang(string $value, int $position): bool
    {
        return $this->options['with_extlang'] &&
            $this->isExtlangPosition($position) &&
            strlen($value) === self::EXTLANG_SUBTAG_LENGTH;
    }

    private function isExtlangPosition(int $position): bool
    {
        return $position === 1;
    }

    private function normalizeExtlang(string $subtag): string
    {
        return strtolower($subtag);
    }

    private function isScript(string $value, int $position): bool
    {
        return $this->options['with_script'] &&
            $this->isScriptPosition($position) &&
            strlen($value) === self::SCRIPT_SUBTAG_LENGTH;
    }

    private function isScriptPosition(int $position): bool
    {
        return $position === 1 ||
            ($this->processed === 1 && $position === 2);
    }

    private function normalizeScript(string $subtag): string
    {
        return ucfirst(strtolower($subtag));
    }

    private function isRegion(string $value, int $position): bool
    {
        return $this->options['with_region'] &&
            $this->isRegionPosition($position) &&
            strlen($value) === self::REGION_SUBTAG_LENGTH;
    }

    private function isRegionPosition(int $position): bool
    {
        return $position === 1 ||
            ($this->processed === 1 && $position === 2) ||
            ($this->processed === 2 && $position === 3);
    }

    private function normalizeRegion(string $subtag): string
    {
        return strtoupper($subtag);
    }
}
