<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\LogHandlerInterface;
use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrieveHeaderLogHandler;
use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrieveNormalizedLanguagesLogHandler;
use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrievePreferredLanguageLogHandler;
use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrievePreferredLanguagesLogHandler;
use Kudashevs\AcceptLanguage\LogProviders\LogHandlers\RetrieveRawLanguagesLogHandler;
use Psr\Log\LoggerInterface;

final class LogProvider
{
    /**
     * Contain a PSR-3 compatible logger.
     */
    private LoggerInterface $logger;

    private array $options = [
    ];

    /**
     * @var array
     */
    private array $handlers = [ // @note can be a mapper
        'retrieve_header' => RetrieveHeaderLogHandler::class,
        'retrieve_raw_languages' => RetrieveRawLanguagesLogHandler::class,
        'retrieve_normalized_languages' => RetrieveNormalizedLanguagesLogHandler::class,
        'retrieve_preferred_languages' => RetrievePreferredLanguagesLogHandler::class,
        'retrieve_preferred_language' => RetrievePreferredLanguageLogHandler::class,
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
     * @param string|array $data
     *
     * @throws InvalidLogEventName
     */
    public function log(string $event, $data): void
    {
        if (!$this->isRegisteredEvent($event)) {
            $this->handleUnexpectedEvent($event);
        }

        $handler = $this->initHandler($event);
        $handler->handle($event, $data);
    }

    protected function isRegisteredEvent(string $event): bool
    {
        return array_key_exists($event, $this->handlers);
    }

    private function initHandler(string $event): LogHandlerInterface
    {
        $handlerClass = $this->handlers[$event];

        return new $handlerClass($this->logger);
    }

    private function handleUnexpectedEvent(string $event): void
    {
        throw new InvalidLogEventName(
            sprintf('The provided event "%s" is invalid.', $event)
        );
    }
}
