<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Psr\Log\LoggerInterface;

class RetrievePreferredLanguageLogPresenter implements LogPresenterInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function present(string $event, $language): void
    {
        $this->logger->info(
            sprintf('Retrieved "%s" resulting language [%s event].', $language, $event)
        );
    }
}
