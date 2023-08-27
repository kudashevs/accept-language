<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

final class RetrieveHeaderLogPresenter implements LogPresenterInterface
{
    private string $event;

    public function __construct(string $event)
    {
        $this->event = $event;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $header
     */
    public function present($header): string
    {
        if ($this->isEmptyData($header)) {
            return sprintf('Warning! An empty header was retrieved [%s event].', $this->event);
        }

        return sprintf('Retrieved "%s" header [%s event].', $header, $this->event);
    }

    private function isEmptyData(string $data): bool
    {
        return trim($data) === '';
    }
}
