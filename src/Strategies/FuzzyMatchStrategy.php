<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Languages\AbstractLanguage;
use Kudashevs\AcceptLanguage\Languages\Language;

class FuzzyMatchStrategy implements MatchStrategyInterface
{
    public function match(array $languages, array $accepted): array
    {
        $result = [];

        foreach ($languages as $language) {
            foreach ($accepted as $accept) {
                if ($this->isFuzzyMatch($accept, $language)) {
                    $result[] = Language::create(
                        $accept->getTag(),
                        $language->getQuality(),
                        $language->getOptions(),
                    );
                }
            }
        }

        return $result;
    }

    protected function isFuzzyMatch(AbstractLanguage $target, AbstractLanguage $source): bool
    {
        $matchingSubtagsNumber = count(array_intersect($target->getSubtags(), $source->getSubtags()));
        $minimumThreshold = count($target->getSubtags());

        return $matchingSubtagsNumber >= $minimumThreshold;
    }
}
