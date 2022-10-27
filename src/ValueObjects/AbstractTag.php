<?php

namespace Kudashevs\AcceptLanguage\ValueObjects;

/**
 * AbstractTag represents an abstraction of a tag with a quality.
 */
interface AbstractTag // @todo rename AbstractLanguage
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
