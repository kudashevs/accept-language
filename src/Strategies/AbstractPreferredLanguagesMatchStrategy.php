<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Language\AbstractLanguage;

/**
 * AbstractPreferredLanguagesMatchStrategy represents an abstraction that finds the matching preferred languages.
 */
interface AbstractPreferredLanguagesMatchStrategy
{
    /**
     * Find and return values from languages that correspond to accepted languages using a matching algorithm.
     *
     * @param array<AbstractLanguage> $languages
     * @param array<AbstractLanguage> $accepted
     * @return array<AbstractLanguage>
     */
    public function retrieve(array $languages, array $accepted): array;
}
