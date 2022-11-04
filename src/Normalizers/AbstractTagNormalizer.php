<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

/**
 * AbstractTagNormalizer represents an abstraction that normalizes a tag to a certain specification.
 */
interface AbstractTagNormalizer
{
    /**
     * Return a normalized tag.
     *
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string;
}
