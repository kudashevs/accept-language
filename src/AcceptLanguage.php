<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\Language\AbstractLanguage;
use Kudashevs\AcceptLanguage\Language\Language;

class AcceptLanguage
{
    /**
     * Contain an original header.
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
     * 'two_letter_only' bool A boolean that defines whether to retrieve only two-letter primary subtags or not.
     * 'use_extlang_subtag' bool A boolean that defines whether to include an extlang subtag in the result or not.
     * 'use_script_subtag' bool A boolean that defines whether to include a script subtag in the result or not.
     * 'use_region_subtag' bool A boolean that defines whether to include a region subtag in the result or not.
     * 'separator' string A string with a character that will be used as a separator in the result.
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
     *     'separator': string
     * }
     */
    protected array $options = [
        'http_accept_language' => '',
        'default_language' => 'en',
        'accepted_languages' => [],
        'exact_match_only' => true,
        'two_letter_only' => true,
        'use_extlang_subtag' => false,
        'use_script_subtag' => true,
        'use_region_subtag' => true,
        'separator' => '_',
    ];

    protected LanguageFactory $factory;

    /**
     * @param array<string, bool|string|array> $options
     * @throws InvalidOptionArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
        $this->initFactory();
    }

    /**
     * @throws InvalidOptionArgumentException
     */
    protected function initOptions(array $options): void
    {
        $validated = $this->retrieveValidOptions($options);

        $this->options = array_merge($this->options, $validated);
    }

    /**
     * @return array<string, bool|string|array>
     *
     * @throws InvalidOptionArgumentException
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
     * @throws InvalidOptionArgumentException
     */
    protected function validateOption(string $name, $value): void
    {
        if (!$this->isValidOptionName($name)) {
            return;
        }

        $externalOptionType = gettype($value);
        $internalOptionType = gettype($this->options[$name]);

        if ($externalOptionType !== $internalOptionType) {
            throw new InvalidOptionArgumentException(
                sprintf(
                    'The option "%s" has a wrong value type %s. This option requires a value of the type %s.',
                    $name,
                    $externalOptionType,
                    $internalOptionType
                )
            );
        }
    }

    protected function isValidOptionName(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    protected function initFactory(): void
    {
        $this->factory = $this->createFactory();
    }

    protected function createFactory(): LanguageFactory
    {
        return new LanguageFactory([
            'separator' => $this->options['separator'],
            'with_extlang' => $this->options['use_extlang_subtag'],
            'with_script' => $this->options['use_script_subtag'],
            'with_region' => $this->options['use_region_subtag'],
        ]);
    }

    /**
     * Retrieve an HTTP Accept-Language header, process its value, find
     * a language of preference, and update the state for further use.
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
        $value = trim($this->options['http_accept_language']) === ''
            ? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']
            : $this->options['http_accept_language'];

        return trim($value);
    }

    protected function findPreferredLanguage(string $header): string
    {
        $applicableLanguages = $this->parseHeader($header);

        $preferredLanguages = $this->retrievePreferredLanguages($applicableLanguages);

        return $this->retrieveLanguage($preferredLanguages);
    }

    /**
     * @return array<AbstractLanguage>
     */
    protected function parseHeader(string $header): array
    {
        /**
         * Some cases do not require further processing as they result in the default language.
         */
        if ($this->isDefaultLanguageCase($header)) {
            return [];
        }

        return $this->parseHeaderWithNormalization($header);
    }

    protected function isDefaultLanguageCase(string $header): bool
    {
        return trim($header) === '' || trim($header) === '*';
    }

    /**
     * Parse an HTTP Accept-Language header, retrieve languages, and prepare them for further processing.
     *
     * @param string $header
     * @return array<AbstractLanguage>
     */
    protected function parseHeaderWithNormalization(string $header): array
    {
        $languages = $this->retrieveLanguages($header);

        return $this->normalizeLanguages($languages);
    }

    protected function retrieveLanguages(string $header): array
    {
        $fallbackQuality = 1;
        $fallbackQualityStep = 0.1;

        $languages = [];
        foreach (explode(',', $header) as $languageRange) {
            /**
             * For more information about language ranges see RFC 4647, Section 2.1.
             */
            $splitLanguageRange = $this->splitLanguageRange($languageRange);

            /** @var array<AbstractLanguage> $languages */
            $languages[] = $this->factory->makeFromLanguageRange(
                $splitLanguageRange,
                $fallbackQuality
            );

            $fallbackQuality -= $fallbackQualityStep;
        }

        return $languages;
    }

    protected function splitLanguageRange(string $range): array
    {
        /**
         * Many of the request header fields for proactive negotiation use a common parameter, named "q" (case-insensitive),
         * to assign a relative "weight" to the preference for that associated kind of content. See RFC 7231, Section 5.3.1.
         */
        return preg_split('/;q=/i', trim($range));
    }

    protected function normalizeLanguages(array $languages): array
    {
        $applicable = $this->getApplicableLanguages($languages);

        /**
         * Sorting by quality is a part of the normalization process.
         */
        usort($applicable, static function ($a, $b) {
            return $b->getQuality() <=> $a->getQuality();
        });

        return $applicable;
    }

    protected function getApplicableLanguages(array $languages): array
    {
        return array_filter($languages, static function ($language) {
            return $language->isValid() && $language->getQuality() > 0;
        });
    }

    protected function retrievePreferredLanguages(array $languages): array
    {
        if ($this->isEmptyAcceptedLanguagesOption()) {
            return $languages;
        }

        $preparedAcceptedLanguagesOption = $this->prepareAcceptedLanguagesForComparison();

        return array_filter($languages, function ($language) use ($preparedAcceptedLanguagesOption) {
            $preparedTag = $this->prepareLanguageForComparison($language->getTag());

            return in_array($preparedTag, $preparedAcceptedLanguagesOption, true);
        });
    }

    protected function isEmptyAcceptedLanguagesOption(): bool
    {
        return count($this->options['accepted_languages']) === 0;
    }

    protected function prepareAcceptedLanguagesForComparison(): array
    {
        return array_map(function ($language) {
            $hyphenatedLanguage = str_replace($this->options['separator'], '-', $language);

            return $this->prepareLanguageForComparison($hyphenatedLanguage);
        }, $this->options['accepted_languages']);
    }

    protected function prepareLanguageForComparison(string $language): string
    {
        $languageTag = $this->factory->makeFromLanguageString($language);

        if ($this->isExactMatchOnlyCase()) {
            return $languageTag->getTag();
        }

        return $languageTag->getPrimarySubtag();
    }

    protected function isExactMatchOnlyCase(): bool
    {
        return $this->options['exact_match_only'];
    }

    protected function retrieveLanguage(array $languages): string
    {
        foreach ($languages as $language) {
            if ($this->isAnyLanguage($language)) {
                return $this->retrieveDefaultLanguage();
            }

            if ($this->isAppropriateLanguage($language)) {
                return $language->getTag();
            }
        }

        return $this->retrieveDefaultLanguage();
    }

    protected function isAnyLanguage(Language $language): bool
    {
        return $language->getTag() === '*';
    }

    protected function isAppropriateLanguage(Language $language): bool
    {
        $primarySubtagMinLength = 2;
        $primarySubtagMaxLength = 3;
        $primarySubtagLength = strlen(
            $language->getPrimarySubtag()
        );

        if ($this->isTwoLetterOnlyCase()) {
            return $primarySubtagLength === 2;
        }

        return $primarySubtagLength >= $primarySubtagMinLength && $primarySubtagLength <= $primarySubtagMaxLength;
    }

    protected function isTwoLetterOnlyCase()
    {
        return $this->options['two_letter_only'];
    }

    protected function retrieveDefaultLanguage(): string
    {
        $formattedDefaultLanguage = $this->factory->makeFromLanguageString(
            $this->options['default_language']
        );

        return $formattedDefaultLanguage->getTag();
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
     * Return the language of preference.
     *
     * @return string
     */
    public function getPreferredLanguage(): string
    {
        return $this->language;
    }

    /**
     * Return the language of preference.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getPreferredLanguage();
    }
}
