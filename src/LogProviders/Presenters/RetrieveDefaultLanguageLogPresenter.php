<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

final class RetrieveDefaultLanguageLogPresenter implements LogPresenterInterface
{
    /**
     * {@inheritDoc}
     *
     * @param string $event
     * @param string $language
     */
    public function present(string $event, $language): string
    {
        if ($this->isEmptyData($language)) {
            return sprintf('Warning! An empty language was returned in the default language case [%s event].', $event);
        }

        return sprintf('Returned "%s" as a default language case [%s event].', $language, $event);
    }

    private function isEmptyData(string $data): bool
    {
        return trim($data) === '';
    }
}
