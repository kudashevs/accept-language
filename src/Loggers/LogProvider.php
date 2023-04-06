<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Loggers;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLoggableEvent;
use Psr\Log\LoggerInterface;

class LogProvider
{
    /**
     * Contain a PSR-3 compatible logger.
     */
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log an event with the provided context.
     *
     * @param string $event
     * @param string $data
     *
     * @throws InvalidLoggableEvent
     */
    public function log(string $event, string $data): void
    {
        switch ($event) {
            case 'retrieve_header':
                $this->handleRetrieveHeader($data);
                break;

            default:
                throw new InvalidLoggableEvent(
                    sprintf('The provided event "%s" is invalid.', $event)
                );
        }
    }

    private function handleRetrieveHeader(string $header): void
    {
        $this->logger->info(
            sprintf('Retrieved a "%s" header.', $header)
        );
    }
}
