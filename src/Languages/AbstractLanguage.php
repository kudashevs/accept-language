<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Languages;

/**
 * AbstractLanguage represents an abstraction of a Language that consists of a language tag and a quality value.
 */
abstract class AbstractLanguage
{
    /**
     * Return the provided options.
     *
     * @return array<string, mixed>
     */
    abstract public function getOptions(): array;

    /**
     * Return the language tag.
     *
     * @return string
     */
    abstract public function getTag(): string;

    /**
     * Return all subtags of the language tag.
     *
     * @return array
     */
    abstract public function getSubtags(): array;

    /**
     * Return a primary subtag of the language tag.
     *
     * @return string
     */
    abstract public function getPrimarySubtag(): string;

    /**
     * Return the quality value.
     *
     * @return int|float
     */
    abstract public function getQuality();

    /**
     * Determine whether a provided language value was valid.
     *
     * @return bool
     */
    abstract public function isValid(): bool;
}
