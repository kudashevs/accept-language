<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\LogPresenterInterface;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveDefaultLanguageLogPresenter;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveHeaderLogPresenter;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveNormalizedLanguagesLogPresenter;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrievePreferredLanguageLogPresenter;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrievePreferredLanguagesLogPresenter;
use Kudashevs\AcceptLanguage\LogProviders\Presenters\RetrieveRawLanguagesLogPresenter;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LogProvider
{
    /**
     * Contain a PSR-3 compatible logger.
     */
    private LoggerInterface $logger;

    private array $options = [
        'log_level' => 'info',
        'log_only' => [],
    ];

    /**
     * @var array
     */
    private array $presenters = [ // @note can be a mapper
        'retrieve_header' => RetrieveHeaderLogPresenter::class,
        'retrieve_default_language' => RetrieveDefaultLanguageLogPresenter::class,
        'retrieve_raw_languages' => RetrieveRawLanguagesLogPresenter::class,
        'retrieve_normalized_languages' => RetrieveNormalizedLanguagesLogPresenter::class,
        'retrieve_preferred_languages' => RetrievePreferredLanguagesLogPresenter::class,
        'retrieve_preferred_language' => RetrievePreferredLanguageLogPresenter::class,
    ];

    /**
     * @param LoggerInterface $logger
     * @param array<string, string|array> $options
     *
     * @throws InvalidOptionType|InvalidLogLevelName
     */
    public function __construct(LoggerInterface $logger, array $options = [])
    {
        $this->initLogger($logger);
        $this->initOptions($options);

        $this->checkValidLogLevel();
        $this->checkValidLogEvents();
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
        $validated = $this->retrieveValidOptions($options);

        $this->options = array_merge($this->options, $validated);
    }

    /**
     * @return array<string, string|array>
     *
     * @throws InvalidOptionType
     */
    protected function retrieveValidOptions(array $options): array
    {
        $allowedOptions = array_intersect_key($options, $this->options);

        foreach ($allowedOptions as $name => $value) {
            $this->validateOption($name, $value);
        }

        return $allowedOptions;
    }

    /**
     * @throws InvalidOptionType
     */
    protected function validateOption(string $name, $value): void
    {
        $externalOptionType = gettype($value);
        $internalOptionType = gettype($this->options[$name]);

        if ($externalOptionType !== $internalOptionType) {
            throw new InvalidOptionType(
                sprintf(
                    'The option "%s" has a wrong value type %s. This option requires a value of the type %s.',
                    $name,
                    $externalOptionType,
                    $internalOptionType
                )
            );
        }
    }

    /**
     * @throws InvalidLogLevelName
     */
    private function checkValidLogLevel(): void
    {
        $validLevels = $this->retrieveValidLogLevels();
        $requestedLevel = strtolower($this->options['log_level']);

        if (!in_array($requestedLevel, $validLevels, true)) {
            throw new InvalidLogLevelName(
                sprintf(
                    'The log level "%s" does not exist. Use %s instead.',
                    $requestedLevel,
                    implode(', ', $validLevels),
                )
            );
        }
    }

    /**
     * @throws InvalidLogEventName
     */
    private function checkValidLogEvents(): void
    {
        if (!$this->isLogOnlyCase()) {
            return;
        }

        $logOnlyEvents = $this->retrieveLogOnlyEvents();
        $registeredEvents = array_flip($this->presenters);

        $difference = array_diff($logOnlyEvents, $registeredEvents);

        if (count($difference) > 0) {
            throw new InvalidLogEventName(
                sprintf(
                    'The log name%s "%s" %s not registered. Use %s instead.',
                    (count($difference) > 1) ? 's' : '',
                    implode(', ', $difference),
                    (count($difference) > 1) ? 'are' : 'is',
                    implode(', ', $registeredEvents),
                )
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function retrieveValidLogLevels(): array
    {
        $levels = (new \ReflectionClass(LogLevel::class))
            ->getConstants();

        return array_map(function ($level) {
            return strtolower($level);
        }, $levels);
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

        if (!$this->isDesiredEvent($event)) {
            return;
        }

        $logLevel = $this->options['log_level'];
        $presenter = $this->initPresenter($event);
        $presentation = $presenter->present($data);

        $this->logger->{$logLevel}($presentation);
    }

    protected function isRegisteredEvent(string $event): bool
    {
        return array_key_exists($event, $this->presenters);
    }

    private function isDesiredEvent(string $event): bool
    {
        if ($this->isLogOnlyCase()) {
            return in_array($event, $this->retrieveLogOnlyEvents());
        }

        return true;
    }

    private function isLogOnlyCase(): bool
    {
        return count($this->options['log_only']) > 0;
    }

    /**
     * @return array<int, string>
     */
    private function retrieveLogOnlyEvents(): array
    {
        return array_map(static function ($event) {
            return trim($event);
        }, $this->options['log_only']);
    }

    private function initPresenter(string $event): LogPresenterInterface
    {
        $presenterClass = $this->presenters[$event];

        return new $presenterClass($event);
    }

    private function handleUnexpectedEvent(string $event): void
    {
        throw new InvalidLogEventName(
            sprintf('The provided event "%s" is invalid.', $event)
        );
    }
}
