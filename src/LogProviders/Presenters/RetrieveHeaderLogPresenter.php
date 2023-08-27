<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Psr\Log\LoggerInterface;

class RetrieveHeaderLogPresenter implements LogPresenterInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function present(string $event, $header): void
    {
        $this->logger->info(
            sprintf('Retrieved "%s" header [%s event].', $header, $event)
        );
    }
}
