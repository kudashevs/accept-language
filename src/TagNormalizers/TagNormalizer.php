<?php

namespace Kudashevs\AcceptLanguage\TagNormalizers;

interface TagNormalizer
{
    /**
     * @param string $tag
     * @return string
     */
    public function normalize(string $tag): string;
}
