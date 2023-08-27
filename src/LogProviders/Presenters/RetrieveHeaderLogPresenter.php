<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

final class RetrieveHeaderLogPresenter implements LogPresenterInterface
{
    /**
     * {@inheritDoc}
     *
     * @param string $event
     * @param string $header
     */
    public function present(string $event, $header): string
    {
        return sprintf('Retrieved "%s" header [%s event].', $header, $event);
    }
}
