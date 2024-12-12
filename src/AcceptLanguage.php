<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidLogEventName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidLogLevelName;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionValue;
use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use Kudashevs\AcceptLanguage\Languages\LanguageInterface;
use Kudashevs\AcceptLanguage\Loggers\DummyLogger;
use Kudashevs\AcceptLanguage\LogProviders\LogProvider;
use Kudashevs\AcceptLanguage\Strategies\ExactMatchStrategy;
use Kudashevs\AcceptLanguage\Strategies\FuzzyMatchStrategy;
use Kudashevs\AcceptLanguage\Strategies\MatchStrategyInterface;
use Psr\Log\LoggerInterface;

class AcceptLanguage
{
    /*
     * The length of the primary subtag is limited to a range of 2 to 3 letters.
     * However, these values might be changed in the future.
    */
    protected const PRIMARY_SUBTAG_MIN_LENGTH = 2;
    protected const PRIMARY_SUBTAG_MAX_LENGTH = 3;

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
    protected LanguageInterface $language;

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
     *     http_accept_language: string,
     *     default_language: string,
     *     accepted_languages: array<array-key, string>,
     *     exact_match_only: bool,
     *     two_letter_only: bool,
     *     use_extlang_subtag: bool,
     *     use_script_subtag: bool,
     *     use_region_subtag: bool,
     *     separator: string,
     *     log_activity: bool,
     *     log_level: string,
     *     log_only: array<array-key, string>,
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
     * @param array{
     *     http_accept_language?: string,
     *     default_language?: string,
     *     accepted_languages?: array<array-key, string>,
     *     exact_match_only?: bool,
     *     two_letter_only?: bool,
     *     use_extlang_subtag?: bool,
     *     use_script_subtag?: bool,
     *     use_region_subtag?: bool,
     *     separator?: string,
     *     log_activity?: bool,
     *     log_level?: string,
     *     log_only?: array<array-key, string>,
     * } $options
     *
     * @throws InvalidOptionType|InvalidOptionValue|InvalidLogLevelName|InvalidLogEventName
     */
    public function __construct(array $options = [])
    {
        $this->initOptionsWithTypeValidation($options);

        $this->initFactory();
        $this->initLogger();

        $this->initResultingState();
        $this->initDefaultLanguageFromOptions();
    }

    /**
     * @param array{http_accept_language?: string, default_language?: string, accepted_languages?: array<array-key, string>, exact_match_only?: bool, two_letter_only?: bool, use_extlang_subtag?: bool, use_script_subtag?: bool, use_region_subtag?: bool, separator?: string, log_activity?: bool, log_level?: string, log_only?: array<array-key, string>} $options
     *
     * @throws InvalidOptionType
     */
    protected function initOptionsWithTypeValidation(array $options): void
    {
        $allowedOptions = $this->retrieveAllowedOptions($options);

        $this->validateOptions($allowedOptions);

        $this->options = array_merge($this->options, $allowedOptions);
    }

    /**
     * @param array{http_accept_language?: string, default_language?: string, accepted_languages?: array<array-key, string>, exact_match_only?: bool, two_letter_only?: bool, use_extlang_subtag?: bool, use_script_subtag?: bool, use_region_subtag?: bool, separator?: string, log_activity?: bool, log_level?: string, log_only?: array<array-key, string>} $options
     * @return array{http_accept_language?: string, default_language?: string, accepted_languages?: array<array-key, string>, exact_match_only?: bool, two_letter_only?: bool, use_extlang_subtag?: bool, use_script_subtag?: bool, use_region_subtag?: bool, separator?: string, log_activity?: bool, log_level?: string, log_only?: array<array-key, string>}
     */
    protected function retrieveAllowedOptions(array $options): array
    {
        return array_intersect_key($options, $this->options);
    }

    /**
     * @param array{http_accept_language?: string, default_language?: string, accepted_languages?: array<array-key, string>, exact_match_only?: bool, two_letter_only?: bool, use_extlang_subtag?: bool, use_script_subtag?: bool, use_region_subtag?: bool, separator?: string, log_activity?: bool, log_level?: string, log_only?: array<array-key, string>} $options
     */
    protected function validateOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->validateOption($name, $value);
        }
    }

    /**
     * @param string|bool|array<array-key, string> $value
     *
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

    protected function initFactory(): void
    {
        $this->factory = $this->createFactory($this->options);
    }

    /**
     * @param array{separator: string, use_extlang_subtag: bool, use_script_subtag: bool, use_region_subtag: bool} $options
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
     * @param array{log_level: string, log_only: array<array-key, string>} $options
     * @return LogProvider
     */
    protected function createLogger(array $options): LogProvider
    {
        return new LogProvider(new DummyLogger(), [
            'log_level' => $options['log_level'],
            'log_only' => $options['log_only'],
        ]);
    }

    protected function initResultingState(): void
    {
        $this->header = '';
        $this->language = DefaultLanguage::createInvalid('', 0);
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
                    'The value "%s" is invalid. The option default_language should contain a valid language tag.',
                    $this->options['default_language']
                )
            );
        }

        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * Retrieve an HTTP Accept-Language header, parse its value, retrieve valid
     * languages, find a preferred language, and retain it for further use.
     *
     * @return void
     */
    public function process(): void
    {
        $header = $this->retrieveHeaderValue();

        $this->header = $header;
        $this->language = $this->findPreferredLanguage($header);
    }

    protected function retrieveHeaderValue(): string
    {
        $header = trim($this->options['http_accept_language']) === ''
            ? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']
            : $this->options['http_accept_language'];

        $this->logger->log('retrieve_header', $header);

        return trim($header);
    }

    protected function findPreferredLanguage(string $header): LanguageInterface
    {
        /*
         * There are several situations that result into the default language
         * so there is no need to continue the header processing any further.
         */
        if ($this->isDefaultLanguageCase($header)) {
            return $this->processDefaultLanguageCase();
        }

        $normalizedLanguages = $this->processHeaderValue($header);

        $preferredLanguages = $this->processNormalizedLanguages($normalizedLanguages);

        return $this->processPreferredLanguages($preferredLanguages);
    }

    protected function isDefaultLanguageCase(string $header): bool
    {
        return $header === '' || $header === '*';
    }

    protected function processDefaultLanguageCase(): LanguageInterface
    {
        $defaultLanguage = $this->retrieveDefaultLanguage();

        $this->logger->log('retrieve_default_language', $defaultLanguage->getTag());

        return $defaultLanguage;
    }

    /**
     * Retrieve and normalize languages from an HTTP Accept-Language header.
     *
     * @param string $header
     * @return array<LanguageInterface>
     */
    protected function processHeaderValue(string $header): array
    {
        $rawLanguages = $this->parseHeaderValue($header);

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
    protected function parseHeaderValue(string $header): array
    {
        return $this->retrieveRawLanguages($header);
    }

    /**
     * @return array<LanguageInterface>
     */
    protected function retrieveRawLanguages(string $header): array
    {
        $fallbackQuality = 1;
        $fallbackQualityStep = 0.1;

        $languages = [];
        foreach (explode(',', $header) as $languageRange) {
            /*
             * For more information about language ranges see RFC 4647, Section 2.1.
             */
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
     * @return array<array-key, string>
     */
    protected function splitLanguageRange(string $range): array
    {
        /*
         * Many of the request header fields for proactive negotiation use a common parameter, named "q" (case-insensitive),
         * to assign a relative "weight" to the preference for that associated kind of content. See RFC 7231, Section 5.3.1.
         */
        $splitRange = preg_split('/;q=/i', trim($range));

        return ($splitRange !== false) ? $splitRange : [];
    }

    /**
     * Return valid languages sorted by the quality value.
     *
     * @param array<LanguageInterface> $languages
     * @return array<LanguageInterface>
     */
    protected function normalizeLanguages(array $languages): array
    {
        $validLanguages = $this->getValidLanguages($languages);

        /*
         * Sorting by quality is a part of the normalization process.
         */
        usort($validLanguages, static function ($a, $b) {
            return $b->getQuality() <=> $a->getQuality();
        });

        return $validLanguages;
    }

    /**
     * @param array<LanguageInterface> $languages
     * @return array<LanguageInterface>
     */
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
     * @param array<LanguageInterface> $languages
     * @return array<LanguageInterface>
     */
    protected function processNormalizedLanguages(array $languages): array
    {
        $preferredLanguages = $this->retrievePreferredLanguages($languages);

        $this->logger->log('retrieve_preferred_languages', $preferredLanguages);

        return $preferredLanguages;
    }

    /**
     * @param array<LanguageInterface> $languages
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
     * @param array<LanguageInterface> $languages
     * @return array<LanguageInterface>
     */
    protected function retrieveAcceptedLanguages(array $languages): array
    {
        $acceptedLanguages = $this->prepareAcceptedLanguagesForMatching();

        $this->logger->log('retrieve_accepted_languages', $acceptedLanguages);

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
    protected function processPreferredLanguages(array $languages): LanguageInterface
    {
        return $this->retrievePreferredLanguage($languages);
    }

    /**
     * @param array<LanguageInterface> $languages
     * @return string
     */
    protected function retrievePreferredLanguage(array $languages): LanguageInterface
    {
        foreach ($languages as $language) {
            if ($this->isAnyLanguage($language)) {
                return $this->processAnyLanguageCase();
            }

            if ($this->isAppropriateLanguage($language)) {
                return $this->processAppropriateLanguageCase($language);
            }
        }

        return $this->processLanguageNotFoundCase();
    }

    protected function processAnyLanguageCase(): LanguageInterface
    {
        $preferredLanguage = $this->retrieveDefaultLanguage();

        $this->logger->log('retrieve_preferred_language', $preferredLanguage->getTag());

        return $preferredLanguage;
    }

    protected function processAppropriateLanguageCase(LanguageInterface $language): LanguageInterface
    {
        $preferredLanguage = $language;

        $this->logger->log('retrieve_preferred_language', $preferredLanguage->getTag());

        return $preferredLanguage;
    }

    protected function processLanguageNotFoundCase(): LanguageInterface
    {
        $this->logger->log('retrieve_preferred_language', '');

        return $this->retrieveDefaultLanguage();
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

    protected function retrieveDefaultLanguage(): LanguageInterface
    {
        return $this->defaultLanguage;
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
     * Return a preferred language value.
     *
     * @return string
     */
    public function getPreferredLanguage(): string
    {
        return $this->language->getTag();
    }

    /**
     * Return a preferred language value.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getPreferredLanguage();
    }

    /**
     * Return a preferred language quality.
     *
     * @return int|float
     */
    public function getPreferredLanguageQuality()
    {
        $quality = $this->language->getQuality();

        if ($quality === 0 || (int)$quality === 1) {
            return (int)$quality;
        }

        return $quality;
    }

    /**
     * Return a preferred language quality.
     *
     * @return int|float
     */
    public function getQuality()
    {
        return $this->getPreferredLanguageQuality();
    }
}
