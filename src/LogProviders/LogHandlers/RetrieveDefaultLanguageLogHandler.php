<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\LogHandlers;

use Psr\Log\LoggerInterface;

class RetrieveDefaultLanguageLogHandler implements LogHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $event
     * @param string $language
     */
    public function handle(string $event, $language): void
    {
        $this->logger->info(
            sprintf('Returned "%s" as a default language case [%s event].', $language, $event)
        );
    }
}
