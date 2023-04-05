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
     * @param array $context
     *
     * @throws InvalidLoggableEvent
     */
    public function log(string $event, array $context): void
    {
        throw new InvalidLoggableEvent(
            sprintf('The provided event "%s" is invalid.', $event)
        );
    }
}
