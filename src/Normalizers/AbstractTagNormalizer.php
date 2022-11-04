<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

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
