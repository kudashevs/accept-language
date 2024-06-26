<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

/**
 * MatchStrategyInterface represents an abstraction that finds the matching preferred languages.
 */
interface MatchStrategyInterface
{
    /**
     * Find and return values from languages that correspond to accepted languages using a matching algorithm.
     *
     * @param array<LanguageInterface> $languages
     * @param array<LanguageInterface> $accepted
     * @return array<LanguageInterface>
     */
    public function match(array $languages, array $accepted): array;
}
