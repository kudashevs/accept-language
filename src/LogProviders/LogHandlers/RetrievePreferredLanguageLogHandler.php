<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\LogHandlers;

use Psr\Log\LoggerInterface;

class RetrievePreferredLanguageLogHandler implements LogHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(string $event, $language): void
    {
        $this->logger->info(
            sprintf('Retrieved "%s" resulting language [%s event].', $language, $event)
        );
    }
}
