<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Language;

interface AbstractLanguage
{
    /**
     * Return a language tag.
     *
     * @return string
     */
    public function getTag(): string;

    /**
     * Return a primary subtag of a language tag.
     *
     * @return string
     */
    public function getPrimarySubtag(): string;

    /**
     * Return a quality value.
     *
     * @return int|float
     */
    public function getQuality();

    /**
     * Determine whether the quality value is valid.
     *
     * @return bool
     */
    public function isValid(): bool;
}
