<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\LogHandlers;

interface LogHandlerInterface
{
    /**
     * Log an event with the provided data.
     *
     * @param string $event
     * @param string|array $data
     */
    public function handle(string $event, $data): void;
}
