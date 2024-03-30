<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

class FuzzyMatchStrategy implements MatchStrategyInterface
{
    public function match(array $languages, array $accepted): array
    {
        $result = [];

        foreach ($languages as $language) {
            foreach ($accepted as $accept) {
                if ($this->isFuzzyMatch($accept, $language)) {
                    $result[] = DefaultLanguage::create(
                        $accept->getTag(),
                        $language->getQuality(),
                        $language->getOptions(),
                    );
                }
            }
        }

        return $result;
    }

    protected function isFuzzyMatch(LanguageInterface $target, LanguageInterface $source): bool
    {
        $matchingSubtagsNumber = count(array_intersect($target->getSubtags(), $source->getSubtags()));
        $minimumThreshold = count($target->getSubtags());

        return $matchingSubtagsNumber >= $minimumThreshold;
    }
}
