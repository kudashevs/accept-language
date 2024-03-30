<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Languages;

/**
 * LanguageInterface is an abstraction of an Entity that can be configured through options.
 */
interface ConfigurableInterface
{
    /**
     * Return the provided options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array;
}
