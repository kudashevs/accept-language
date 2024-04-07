<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

/**
 * LogPresenterInterface represents an abstraction that generates a representation of data sent for logging.
 */
interface LogPresenterInterface
{
    /**
     * Prepare the representation of an event with provided data.
     *
     * @param string|array<LanguageInterface> $data
     * @return string
     */
    public function present($data): string;
}
