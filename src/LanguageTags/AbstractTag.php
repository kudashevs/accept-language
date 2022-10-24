<?php

namespace Kudashevs\AcceptLanguage\LanguageTags;

interface AbstractTag
{
    /**
     * Return the tag value.
     *
     * @return string
     */
    public function getTag(): string;

    /**
     * Return the quality value.
     *
     * @return float
     */
    public function getQuality(): float;
}
