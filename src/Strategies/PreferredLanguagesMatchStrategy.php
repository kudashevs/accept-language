<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Language\Language;

class PreferredLanguagesMatchStrategy implements AbstractPreferredLanguagesMatchStrategy
{
    public function retrieve(array $languages, array $accepted): array
    {
        $result = [];

        foreach ($languages as $language) {
            foreach ($accepted as $accept) {
                if ($this->isAcceptedLanguageMatch($accept->getSubtags(), $language->getSubtags())) {
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

    protected function isAcceptedLanguageMatch(array $accepted, array $language): bool
    {
        return count(array_intersect($accepted, $language)) >= count($accepted);
    }
}
