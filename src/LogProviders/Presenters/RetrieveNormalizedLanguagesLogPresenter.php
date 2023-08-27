<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Language\AbstractLanguage;

final class RetrieveNormalizedLanguagesLogPresenter implements LogPresenterInterface
{
    /**
     * {@inheritDoc}
     *
     * @param string $event
     * @param array<AbstractLanguage> $languages
     */
    public function present(string $event, $languages): string
    {
        return sprintf(
            'Retrieved "%s" normalized languages [%s event].',
            $this->processLanguages($languages),
            $event,
        );
    }

    private function processLanguages(array $languages): string
    {
        return implode(',', array_map(static function (AbstractLanguage $lang) {
            return $lang->getTag() . ';q=' . $lang->getQuality();
        }, $languages));
    }
}
