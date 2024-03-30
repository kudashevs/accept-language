<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionValue;
use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Languages\LanguageInterface;
use Kudashevs\AcceptLanguage\Loggers\DummyLogger;
use Kudashevs\AcceptLanguage\LogProviders\LogProvider;
use Kudashevs\AcceptLanguage\Strategies\ExactMatchStrategy;
use Kudashevs\AcceptLanguage\Strategies\FuzzyMatchStrategy;
use Kudashevs\AcceptLanguage\Strategies\MatchStrategyInterface;
use Psr\Log\LoggerInterface;

class AcceptLanguage
{
    // The length of the primary language subtag is limited from 2 to 3 letters.
    // However, we might want to change these values in the future.
    protected const PRIMARY_SUBTAG_MIN_LENGTH = 2;
    protected const PRIMARY_SUBTAG_MAX_LENGTH = 3;

    /**
     * This fallback will be used as the default language value when
     * a `default_language` option contains an invalid language tag.
     */
    private const FALLBACK_LANGUAGE = 'en';

    /**
     * The Factory is responsible for creating a Language from different sources.
     */
    protected LanguageFactory $factory;

    /**
     * The LogProvider is a convenient abstraction over the logger.
     */
    protected LogProvider $logger;

    /**
     * Contain a default language for different default language cases.
     */
    protected LanguageInterface $defaultLanguage;

    /**
     * Contain an original HTTP Accept-Language header.
     */
    protected string $header;

    /**
     * Contain a found language of preference.
     */
    protected string $language;

    /**
     * 'http_accept_language' string A string with a custom HTTP Accept-Language header.
     * 'default_language' string A string with a default preferred language value.
     * 'accepted_languages' array An array with a list of supported languages.
     * 'exact_match_only' bool A boolean that defines whether to retrieve only languages that match exactly the supported languages.
     * 'two_letter_only' bool A boolean that defines whether to retrieve only two-letter primary subtag or not.
     * 'use_extlang_subtag' bool A boolean that defines whether to include an extlang subtag in the result or not.
     * 'use_script_subtag' bool A boolean that defines whether to include a script subtag in the result or not.
     * 'use_region_subtag' bool A boolean that defines whether to include a region subtag in the result or not.
     * 'separator' string A string with a character that will be used as a separator in the result.
     * 'log_activity' bool A boolean that defines whether to log the activity of the package or not.
     * 'log_level' string A string with a PSR-3 compatible log level value.
     * 'log_only' array An array with a list of log only events.
     *
     * @var array{
     *     'http_accept_language': string,
     *     'default_language': string,
     *     'accepted_languages': array<int, string>,
     *     'exact_match_only': bool,
     *     'two_letter_only': bool,
     *     'use_extlang_subtag': bool,
     *     'use_script_subtag': bool,
     *     'use_region_subtag': bool,
     *     'separator': string,
     *     'log_activity': bool,
     *     'log_level': string,
     *     'log_only': array<int, string>,
     * }
     */
    protected array $options = [
        'http_accept_language' => '',
        'default_language' => 'en',
        'accepted_languages' => [],
        'exact_match_only' => false,
        'two_letter_only' => true,
        'use_extlang_subtag' => false,
        'use_script_subtag' => false,
        'use_region_subtag' => true,
        'separator' => '_',
        'log_activity' => false,
        'log_level' => 'info',
        'log_only' => [],
    ];

    /**
     * @param array<string, bool|string|array> $options
     *
     * @throws InvalidOptionType|InvalidOptionValue|InvalidLogLevelName|InvalidLogEventName
     */
    public function __construct(array $options = [])
    {
        $this->initOptionsWithTypeValidation($options);

        $this->initFactory();
        $this->initLogger();

        $this->initDefaultLanguageFromOptions();
    }

    /**
     * @throws InvalidOptionType
     */
    protected function initOptionsWithTypeValidation(array $options): void
    {
        $validated = $this->retrieveOptionsWithTypeValidation($options);

        $this->options = array_merge($this->options, $validated);
    }

    /**
     * @return array<string, bool|string|array>
     *
     * @throws InvalidOptionType
     */
    protected function retrieveOptionsWithTypeValidation(array $options): array
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
     * @throws InvalidOptionValue
     */
    protected function initDefaultLanguageFromOptions(): void
    {
        $defaultLanguage = $this->factory->makeFromLanguageString(
            $this->options['default_language']
        );

        if (!$defaultLanguage->isValid()) {
            throw new InvalidOptionValue(
                sprintf(
                    'The value "%s" is invalid. The option default_language should contain a valid Language tag.',
                    $this->options['default_language']
                )
            );
        }

        $this->defaultLanguage = $defaultLanguage;
    }

    protected function initFactory(): void
    {
        $this->factory = $this->createFactory($this->options);
    }

    /**
     * @param array<string, bool|string> $options
     * @return LanguageFactory
     */
    protected function createFactory(array $options): LanguageFactory
    {
        return new LanguageFactory([
            'separator' => $options['separator'],
            'with_extlang' => $options['use_extlang_subtag'],
            'with_script' => $options['use_script_subtag'],
            'with_region' => $options['use_region_subtag'],
        ]);
    }

    protected function initLogger(): void
    {
        $this->logger = $this->createLogger($this->options);
    }

    /**
     * @param array<string, string> $options
     * @return LogProvider
     */
    protected function createLogger(array $options): LogProvider
    {
        return new LogProvider(new DummyLogger(), [
            'log_level' => $options['log_level'],
            'log_only' => $options['log_only'],
        ]);
    }

    /**
     * Retrieve an HTTP Accept-Language header, parse its value, retrieve valid
     * languages, find a preferred language, and retain it for further use.
     *
     * @return void
     */
    public function process(): void
    {
        $header = $this->retrieveAcceptLanguageHeader();

        $this->header = $header;
        $this->language = $this->findPreferredLanguage($header);
    }

    protected function retrieveAcceptLanguageHeader(): string
    {
        $header = trim($this->options['http_accept_language']) === ''
            ? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']
            : $this->options['http_accept_language'];

        $this->logger->log('retrieve_header', $header);

        return trim($header);
    }

    protected function findPreferredLanguage(string $header): string
    {
        // There are several situations when there is no need to continue
        // further processing as they result into the default language.
        if ($this->isDefaultLanguageCase($header)) {
            return $this->processDefaultLanguageCase();
        }

        $normalizedLanguages = $this->processAcceptLanguageValue($header);

        $preferredLanguages = $this->processNormalizedLanguages($normalizedLanguages);

        return $this->processPreferredLanguages($preferredLanguages);
    }

    protected function isDefaultLanguageCase(string $header): bool
    {
        return $header === '' || $header === '*';
    }

    protected function processDefaultLanguageCase(): string
    {
        $defaultLanguage = $this->retrieveDefaultLanguage();

        $this->logger->log('retrieve_default_language', $defaultLanguage);

        return $defaultLanguage;
    }

    /**
     * Retrieve and normalize languages from an HTTP Accept-Language header.
     *
     * @param string $header
     * @return array<LanguageInterface>
     */
    protected function processAcceptLanguageValue(string $header): array
    {
        $rawLanguages = $this->parseAcceptLanguageValue($header);

        $this->logger->log('retrieve_raw_languages', $rawLanguages);

        $normalizedLanguages = $this->normalizeLanguages($rawLanguages);

        $this->logger->log('retrieve_normalized_languages', $normalizedLanguages);

        return $normalizedLanguages;
    }

    /**
     * Parse a header value and return raw unordered languages.
     *
     * @return array<LanguageInterface>
     */
    protected function parseAcceptLanguageValue(string $header): array
    {
        return $this->retrieveRawLanguages($header);
    }

    protected function retrieveRawLanguages(string $header): array
    {
        $fallbackQuality = 1;
        $fallbackQualityStep = 0.1;

        $languages = [];
        foreach (explode(',', $header) as $languageRange) {
            // For more information about language ranges see RFC 4647, Section 2.1.
            $splitLanguageRange = $this->splitLanguageRange($languageRange);

            /** @var array<LanguageInterface> $languages */
            $languages[] = $this->factory->makeFromLanguageRange(
                $splitLanguageRange,
                $fallbackQuality
            );

            $fallbackQuality -= $fallbackQualityStep;
        }

        return $languages;
    }

    /**
     * @return array<int, string>
     */
    protected function splitLanguageRange(string $range): array
    {
        // Many of the request header fields for proactive negotiation use a common parameter, named "q" (case-insensitive),
        // to assign a relative "weight" to the preference for that associated kind of content. See RFC 7231, Section 5.3.1.
        return preg_split('/;q=/i', trim($range));
    }

    /**
     * Return valid languages sorted by the quality value.
     *
     * @return array<LanguageInterface>
     */
    protected function normalizeLanguages(array $languages): array
    {
        $validLanguages = $this->getValidLanguages($languages);

        // Sorting by quality is a part of the normalization process.
        usort($validLanguages, static function ($a, $b) {
            return $b->getQuality() <=> $a->getQuality();
        });

        return $validLanguages;
    }

    protected function getValidLanguages(array $languages): array
    {
        return array_filter($languages, static function ($language) {
            return $language->isValid() && $language->getQuality() > 0;
        });
    }

    /**
     * Return the found preferred languages. A preferred language is a
     * valid language that was accepted and met all matching criteria.
     *
     * @param array $languages
     * @return array<LanguageInterface>
     */
    protected function processNormalizedLanguages(array $languages): array
    {
        $preferredLanguages = $this->retrievePreferredLanguages($languages);

        $this->logger->log('retrieve_preferred_languages', $preferredLanguages);

        return $preferredLanguages;
    }

    /**
     * @return array<LanguageInterface>
     */
    protected function retrievePreferredLanguages(array $languages): array
    {
        if ($this->isAnyLanguageAccepted()) {
            return $languages;
        }

        return $this->retrieveAcceptedLanguages($languages);
    }

    protected function isAnyLanguageAccepted(): bool
    {
        return count($this->options['accepted_languages']) === 0;
    }

    /**
     * @return array<LanguageInterface>
     */
    protected function retrieveAcceptedLanguages(array $languages): array
    {
        $acceptedLanguages = $this->prepareAcceptedLanguagesForMatching();

        return $this->resolveMatchingStrategy()->match(
            $languages,
            $acceptedLanguages,
        );
    }

    protected function resolveMatchingStrategy(): MatchStrategyInterface
    {
        return $this->isExactMatchOnlyCase()
            ? new ExactMatchStrategy()
            : new FuzzyMatchStrategy();
    }

    protected function isExactMatchOnlyCase(): bool
    {
        return $this->options['exact_match_only'];
    }

    /**
     * @return array<LanguageInterface>
     */
    protected function prepareAcceptedLanguagesForMatching(): array
    {
        return array_map(function ($language) {
            return $this->factory->makeFromLanguageString($language);
        }, $this->options['accepted_languages']);
    }

    /**
     * Return a preferred language from the preferred languages. If none of
     * the languages were appropriate, the default language will be returned.
     *
     * @param array<LanguageInterface> $languages
     * @return string
     */
    protected function processPreferredLanguages(array $languages): string
    {
        $preferredLanguage = $this->retrievePreferredLanguage($languages);

        if (is_null($preferredLanguage)) {
            $this->logger->log('retrieve_preferred_language', '');

            return $this->retrieveDefaultLanguage();
        }

        $this->logger->log('retrieve_preferred_language', $preferredLanguage);

        return $preferredLanguage;
    }

    /**
     * @param array<LanguageInterface> $languages
     * @return string|null
     */
    protected function retrievePreferredLanguage(array $languages): ?string
    {
        foreach ($languages as $language) {
            if ($this->isAnyLanguage($language)) {
                return $this->retrieveDefaultLanguage();
            }

            if ($this->isAppropriateLanguage($language)) {
                return $language->getTag();
            }
        }

        return null;
    }

    protected function isAnyLanguage(LanguageInterface $language): bool
    {
        return $language->getTag() === '*';
    }

    protected function isAppropriateLanguage(LanguageInterface $language): bool
    {
        $primarySubtagLength = strlen(
            $language->getPrimarySubtag()
        );

        if ($this->isTwoLetterOnlyCase()) {
            return $primarySubtagLength === 2;
        }

        return $primarySubtagLength >= static::PRIMARY_SUBTAG_MIN_LENGTH
            && $primarySubtagLength <= static::PRIMARY_SUBTAG_MAX_LENGTH;
    }

    protected function isTwoLetterOnlyCase(): bool
    {
        return $this->options['two_letter_only'];
    }

    protected function retrieveDefaultLanguage(): string
    {
        $defaultLanguage = $this->factory->makeFromLanguageString(
            $this->options['default_language']
        );

        return $this->isAppropriateLanguage($defaultLanguage)
            ? $defaultLanguage->getTag()
            : self::FALLBACK_LANGUAGE;
    }

    /**
     * Replace a default logger.
     *
     * @param LoggerInterface $logger
     */
    public function useLogger(LoggerInterface $logger): void
    {
        if ($this->isLogActivityAllowed()) {
            $this->logger = new LogProvider($logger, $this->options);
        }
    }

    protected function isLogActivityAllowed(): bool
    {
        return $this->options['log_activity'] === true;
    }

    /**
     * Return the original HTTP Accept-Language header.
     *
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * Return a preferred language from an HTTP Accept-Language header.
     *
     * @return string
     */
    public function getPreferredLanguage(): string
    {
        return $this->language;
    }

    /**
     * Return a preferred language from an HTTP Accept-Language header.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getPreferredLanguage();
    }
}
