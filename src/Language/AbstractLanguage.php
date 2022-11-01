<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Language;

interface AbstractLanguage
{
    /**
     * @return string
     */
    public function getTag(): string;

    /**
     * @return string
     */
    public function getPrimarySubtag(): string;

    /**
     * @return int|float
     */
    public function getQuality();

    /**
     * @return bool
     */
    public function isValid(): bool;
}
