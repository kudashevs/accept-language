<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

final class RetrievePreferredLanguageLogPresenter implements LogPresenterInterface
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
            return sprintf('Warning! An empty resulting language was retrieved [%s event].', $event);
        }

        return sprintf('Retrieved "%s" resulting language [%s event].', $language, $event);
    }

    private function isEmptyData(string $data): bool
    {
        return trim($data) === '';
    }
}
