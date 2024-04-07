<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Languages;

/**
 * LanguageInterface is an abstraction of a Language that consists of a language tag and a quality value.
 */
interface LanguageInterface
{
    /**
     * Return the provided options.
     *
     * @return array{separator: string, with_extlang: bool, with_script: bool, with_region: bool, fallback: int|float}
     */
    public function getOptions(): array;

    /**
     * Return the language tag.
     *
     * @return string
     */
    public function getTag(): string;

    /**
     * Return all subtags of the language tag.
     *
     * @return array<array-key, string>
     */
    public function getSubtags(): array;

    /**
     * Return a primary subtag of the language tag.
     *
     * @return string
     */
    public function getPrimarySubtag(): string;

    /**
     * Return the quality value.
     *
     * @return int|float
     */
    public function getQuality();

    /**
     * Determine whether a provided language value was valid.
     *
     * @return bool
     */
    public function isValid(): bool;
}
