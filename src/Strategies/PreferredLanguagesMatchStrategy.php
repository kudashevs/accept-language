<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Language\AbstractLanguage;
use Kudashevs\AcceptLanguage\Language\Language;

class PreferredLanguagesMatchStrategy implements MatchStrategyInterface
{
    public function match(array $languages, array $accepted): array
    {
        $result = [];

        foreach ($languages as $language) {
            foreach ($accepted as $accept) {
                if ($this->isAcceptedLanguageMatch($accept, $language)) {
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

    protected function isAcceptedLanguageMatch(AbstractLanguage $accepted, AbstractLanguage $language): bool
    {
        $matchingSubtags = count(array_intersect($accepted->getSubtags(), $language->getSubtags()));
        $matchingThreshold = count($accepted->getSubtags());

        return $matchingSubtags >= $matchingThreshold;
    }
}
