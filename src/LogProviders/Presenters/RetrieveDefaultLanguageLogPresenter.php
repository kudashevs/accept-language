<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

final class RetrieveDefaultLanguageLogPresenter implements LogPresenterInterface
{
    private string $event;

    public function __construct(string $event)
    {
        $this->event = $event;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $language
     */
    public function present($language): string
    {
        if ($this->isEmptyData($language)) {
            return sprintf(
                'Warning! An empty language was returned in the default language case [%s event].',
                $this->event);
        }

        return sprintf('Returned "%s" as a default language case [%s event].', $language, $this->event);
    }

    private function isEmptyData(string $data): bool
    {
        return trim($data) === '';
    }
}
