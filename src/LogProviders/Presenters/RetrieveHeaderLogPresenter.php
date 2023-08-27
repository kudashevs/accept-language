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
        if ($this->isEmptyData($header)) {
            return sprintf('Warning! An empty header was retrieved [%s event].', $event);
        }

        return sprintf('Retrieved "%s" header [%s event].', $header, $event);
    }

    private function isEmptyData(string $data): bool
    {
        return trim($data) === '';
    }
}
