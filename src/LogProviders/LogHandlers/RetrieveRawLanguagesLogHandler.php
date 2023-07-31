<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\LogHandlers;

use Psr\Log\LoggerInterface;

class RetrieveRawLanguagesLogHandler implements LogHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(string $event, $languages): void
    {
        $this->logger->info(
            sprintf('Retrieved "%s" raw languages [%s event].', $languages, $event)
        );
    }
}
