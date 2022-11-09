<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Language\AbstractLanguage;

interface AbstractPreferredLanguagesMatchStrategy
{
    /**
     * @param array<AbstractLanguage> $languages
     * @param array<AbstractLanguage> $accepted
     * @return array<AbstractLanguage
     */
    public function retrieve(array $languages, array $accepted): array;
}
