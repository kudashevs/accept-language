<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Loggers;

use Psr\Log\LoggerInterface;

class DummyLogger implements LoggerInterface
{
    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = []): void
    {
    }
}
