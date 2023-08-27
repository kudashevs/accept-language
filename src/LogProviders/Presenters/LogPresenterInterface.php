<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

interface LogPresenterInterface
{
    /**
     * Prepare the representation of an event with provided data.
     *
     * @param string|array $data
     * @return string
     */
    public function present($data): string;
}
