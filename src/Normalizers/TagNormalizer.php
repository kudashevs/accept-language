<?php

namespace Kudashevs\AcceptLanguage\Normalizers;

interface TagNormalizer
{
    /**
     * Return a normalized tag.
     *
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string;
}
