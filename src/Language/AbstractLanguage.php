<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Language;

/**
 * AbstractLanguage represents an abstraction of a language that consists of a language tag and a quality value.
 */
interface AbstractLanguage
{
    /**
     * Return provided options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * Return a language tag.
     *
     * @return string
     */
    public function getTag(): string;

    /**
     * Return subtags of a language tag.
     *
     * @return array
     */
    public function getSubtags(): array;

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
