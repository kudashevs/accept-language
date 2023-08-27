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
        return sprintf('Retrieved "%s" resulting language [%s event].', $language, $event);
    }
}
