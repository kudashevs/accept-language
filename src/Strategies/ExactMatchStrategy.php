<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

class ExactMatchStrategy implements MatchStrategyInterface
{
    public function match(array $languages, array $accepted): array
    {
        return array_uintersect($languages, $accepted, function ($a, $b) {
            return $a->getTag() <=> $b->getTag();
        });
    }
}
