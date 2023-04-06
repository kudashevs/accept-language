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

            case 'retrieve_raw_languages':
                $this->handleRetrieveRawLanguages($data);
                break;

            case 'retrieve_normalized_languages':
                $this->handleRetrieveNormalizedLanguages($data);
                break;

            case 'retrieve_preferred_languages':
                $this->handleRetrievePreferredLanguages($data);
                break;

            case 'retrieve_preferred_language':
                $this->handleRetrievePreferredLanguage($data);
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

    private function handleRetrieveRawLanguages(string $languages): void
    {
        $this->logger->info(
            sprintf('Raw languages "%s".', $languages)
        );
    }

    private function handleRetrieveNormalizedLanguages(string $languages): void
    {
        $this->logger->info(
            sprintf('Retrieved languages "%s".', $languages)
        );
    }

    private function handleRetrievePreferredLanguages(string $languages): void
    {
        $this->logger->info(
            sprintf('Preferred languages "%s".', $languages)
        );
    }

    private function handleRetrievePreferredLanguage(string $language): void
    {
        $this->logger->info(
            sprintf('Retrieved a "%s" preferred language.', $language)
        );
    }
}
