<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Loggers;

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
}
