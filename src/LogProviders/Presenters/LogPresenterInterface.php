<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

interface LogPresenterInterface
{
    /**
     * Prepare the representation of an event with provided data.
     *
     * @param string $event
     * @param string|array $data
     */
    public function present(string $event, $data): void;
}
