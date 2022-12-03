<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Language;

/**
 * AbstractLanguage represents an abstraction of a language that consists of a language tag and a quality value.
 */
abstract class AbstractLanguage
{
    /**
     * Return provided options.
     *
     * @return array<string, mixed>
     */
    abstract public function getOptions(): array;

    /**
     * Return a language tag.
     *
     * @return string
     */
    abstract public function getTag(): string;

    /**
     * Return subtags of a language tag.
     *
     * @return array
     */
    abstract public function getSubtags(): array;

    /**
     * Return a primary subtag of a language tag.
     *
     * @return string
     */
    abstract public function getPrimarySubtag(): string;

    /**
     * Return a quality value.
     *
     * @return int|float
     */
    abstract public function getQuality();

    /**
     * Determine whether the quality value is valid.
     *
     * @return bool
     */
    abstract public function isValid(): bool;
}
