<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

final class RetrievePreferredLanguageLogPresenter implements LogPresenterInterface
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
            return sprintf('Warning! An empty resulting language was retrieved [%s event].', $this->event);
        }

        return sprintf('Retrieved "%s" resulting language [%s event].', $language, $this->event);
    }

    private function isEmptyData(string $data): bool
    {
        return trim($data) === '';
    }
}
