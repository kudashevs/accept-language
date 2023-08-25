<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

/**
 * TagNormalizerInterface represents an abstraction that normalizes a tag value to the certain specification.
 */
interface TagNormalizerInterface
{
    /**
     * Perform a normalization process and return a normalized tag.
     *
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string;
}
