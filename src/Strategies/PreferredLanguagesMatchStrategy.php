<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Strategies;

use Kudashevs\AcceptLanguage\Language\AbstractLanguage;
use Kudashevs\AcceptLanguage\Language\Language;

class PreferredLanguagesMatchStrategy implements AbstractPreferredLanguagesMatchStrategy
{
    public function retrieve(array $languages, array $accepted): array
    {
        $result = [];

        foreach ($languages as $language) {
            foreach ($accepted as $accept) {
                $preparedLanguage = $this->prepareLanguageForComparison($language);
                $preparedAccepted = $this->prepareLanguageForComparison($accept);

                if ($this->isIntersected($preparedAccepted, $preparedLanguage)) {
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

    protected function prepareLanguageForComparison(AbstractLanguage $language): array
    {
        return $language->getSubtags();
    }

    protected function isIntersected(array $intersecting, array $intersected): bool
    {
        return count(array_intersect($intersecting, $intersected)) >= count($intersecting);
    }
}
