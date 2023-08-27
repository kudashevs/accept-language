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
        return sprintf('Returned "%s" as a default language case [%s event].', $language, $event);
    }
}
