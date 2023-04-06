<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Loggers;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLoggableEvent;
use Psr\Log\LoggerInterface;

final class LogProvider
{
    /**
     * Contain a PSR-3 compatible logger.
     */
    private LoggerInterface $logger;

    /**
     * 'retrieve_header' bool A boolean that defines whether to handle the `retrieve_header` event.
     * 'retrieve_raw_languages' bool A boolean that defines whether to handle the `retrieve_raw_languages` event.
     * 'retrieve_normalized_languages' bool A boolean that defines whether to handle the `retrieve_normalized_languages` event.
     * 'retrieve_preferred_languages' bool A boolean that defines whether to handle the `retrieve_preferred_languages` event.
     * 'retrieve_preferred_language' bool A boolean that defines whether to handle the `retrieve_preferred_language` event.
     *
     * @var array{
     *     'retrieve_header': bool,
     *     'retrieve_raw_languages': bool,
     *     'retrieve_normalized_languages': bool,
     *     'retrieve_preferred_languages': bool,
     *     'retrieve_preferred_language': bool,
     * }
     */
    private array $options = [
        'retrieve_header' => true,
        'retrieve_raw_languages' => true,
        'retrieve_normalized_languages' => true,
        'retrieve_preferred_languages' => true,
        'retrieve_preferred_language' => true,
    ];

    /**
     * @param LoggerInterface $logger
     * @param array<string, bool> $options
     */
    public function __construct(LoggerInterface $logger, array $options = [])
    {
        $this->initLogger($logger);
        $this->initOptions($options);
    }

    private function initLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param array<string, bool> $options
     */
    private function initOptions(array $options): void
    {
        $applicable = $this->retrieveApplicableOptions($options);

        $this->options = array_merge($this->options, $applicable);
    }

    /**
     * @param array<string, bool> $options
     * @return array<string, bool>
     */
    private function retrieveApplicableOptions(array $options): array
    {
        return array_filter($options, 'is_bool');
    }

    /**
     * Log an event with the provided data. The supported events are:
     *
     * 'retrieve_header' allows logging a retrieved HTTP Accept-Language header
     * 'retrieve_raw_languages' allows logging retrieved languages before normalization process
     * 'retrieve_normalized_languages' allows logging retrieved languages after normalization process
     * 'retrieve_preferred_languages' allows logging the preferred languages that match the accepted languages
     * 'retrieve_preferred_language' allows logging the resulting preferred language
     *
     * @param string $event
     * @param string $data
     *
     * @throws InvalidLoggableEvent
     */
    public function log(string $event, string $data): void
    {
        if ($this->shouldSkipHandleEvent($event)) {
            return;
        }

        switch ($event) {
            case 'retrieve_header':
                $this->handleRetrieveHeader($event, $data);
                break;

            case 'retrieve_raw_languages':
                $this->handleRetrieveRawLanguages($event, $data);
                break;

            case 'retrieve_normalized_languages':
                $this->handleRetrieveNormalizedLanguages($event, $data);
                break;

            case 'retrieve_preferred_languages':
                $this->handleRetrievePreferredLanguages($event, $data);
                break;

            case 'retrieve_preferred_language':
                $this->handleRetrievePreferredLanguage($event, $data);
                break;

            default:
                $this->handleUnexpectedEvent($event);
        }
    }

    private function shouldSkipHandleEvent(string $event): bool
    {
        return array_key_exists($event, $this->options) && $this->options[$event] === false;
    }

    private function handleRetrieveHeader(string $event, string $header): void
    {
        $this->logger->info(
            sprintf('Retrieved a "%s" header on %s.', $header, $event)
        );
    }

    private function handleRetrieveRawLanguages(string $event, string $languages): void
    {
        $this->logger->info(
            sprintf('Raw languages "%s" on %s.', $languages, $event)
        );
    }

    private function handleRetrieveNormalizedLanguages(string $event, string $languages): void
    {
        $this->logger->info(
            sprintf('Retrieved languages "%s" on %s.', $languages, $event)
        );
    }

    private function handleRetrievePreferredLanguages(string $event, string $languages): void
    {
        $this->logger->info(
            sprintf('Preferred languages "%s" on %s.', $languages, $event)
        );
    }

    private function handleRetrievePreferredLanguage(string $event, string $language): void
    {
        $this->logger->info(
            sprintf('Retrieved a preferred language "%s" on %s.', $language, $event)
        );
    }

    private function handleUnexpectedEvent(string $event): void
    {
        throw new InvalidLoggableEvent(
            sprintf('The provided event "%s" is invalid.', $event)
        );
    }
}
