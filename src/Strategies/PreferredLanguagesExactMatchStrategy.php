<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

class PreferredLanguagesExactMatchStrategy implements AbstractPreferredLanguagesMatchStrategy
{
    public function retrieve(array $languages, array $accepted): array
    {
        return array_uintersect($languages, $accepted, function ($a, $b) {
            return $a->getTag() <=> $b->getTag();
        });
    }
}
