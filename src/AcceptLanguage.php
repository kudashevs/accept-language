<?php

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use Kudashevs\AcceptLanguage\ValueObjects\Language;

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
     * @var array[
     *  'http_accept_language' string A string with a custom HTTP Accept-Language header.
     *  'default_language' string A string with a default preferred language value.
     *  'accepted_languages' array An array with a list of supported languages.
     *  'two_letter_only' bool A boolean which defines whether to use only the two-letter codes or not.
     *  'separator' string A string with a character that will be used as the separator in the result.
     * ]
     */
    protected array $options = [
        'http_accept_language' => '',
        'default_language' => 'en',
        'accepted_languages' => [],
        'two_letter_only' => true,
        'separator' => '_',
    ];

    protected LanguageFactory $factory;

    /**
     * @param array $options
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
        if (!array_key_exists($name, $this->options)) {
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

    protected function initFactory(): void
    {
        $this->factory = $this->createFactory();
    }

    protected function createFactory(): LanguageFactory
    {
        return new LanguageFactory([
            'separator' => $this->options['separator'],
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
        $languageTags = $this->parseHeader($header);

        $filteredLanguageTags = $this->excludeUnwanted($languageTags);

        return $this->retrieveLanguage($filteredLanguageTags);
    }

    protected function parseHeader(string $header): array
    {
        if (
            $this->isEmpty($header) ||
            $this->isWildcard($header)
        ) {
            return [];
        }

        return $this->parseHeaderWithNormalization($header);
    }

    protected function isEmpty(string $header): bool
    {
        return $header === '';
    }

    protected function isWildcard(string $header): bool
    {
        return $header === '*';
    }

    /**
     * Parse an HTTP Accept-Language header and perform the transition from language ranges (raw and not well-formed
     * data) to language tags (validated and well-formed data). For more information see RFC 4647, Section 2.1.
     *
     * @param string $header
     * @return array{lang: string, quality: float}
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
             * Many of the request header fields for proactive negotiation use a common parameter, named "q" (case-insensitive),
             * to assign a relative "weight" to the preference for that associated kind of content. See RFC 7231, Section 5.3.1.
             */
            $splitLanguageRange = preg_split('/;q=/i', trim($languageRange));

            /** @var Language[] $languages */
            $languages[] = $this->factory->makeFromLanguageRange(
                $splitLanguageRange,
                $fallbackQuality
            );

            $fallbackQuality -= $fallbackQualityStep;
        }

        return $languages;
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

    protected function excludeUnwanted(array $languages): array
    {
        return $this->excludeNotAcceptedLanguages($languages);
    }

    protected function excludeNotAcceptedLanguages(array $languages): array
    {
        if (!$this->hasAcceptedLanguages()) {
            return $languages;
        }

        $preparedAcceptedLanguages = $this->prepareAcceptedLanguagesForComparison();

        return array_filter($languages, function ($value) use ($preparedAcceptedLanguages) {
            $language = $this->prepareLanguageForComparison($value->getTag());

            return in_array($language, $preparedAcceptedLanguages, true);
        });
    }

    protected function hasAcceptedLanguages(): bool
    {
        return count($this->options['accepted_languages']) !== 0;
    }

    protected function prepareAcceptedLanguagesForComparison(): array
    {
        return array_map(function ($language) {
            return $this->prepareLanguageForComparison($language);
        }, $this->options['accepted_languages']);
    }

    protected function prepareLanguageForComparison(string $language): string
    {
        return strtolower(str_replace('_', '-', $language));
    }

    protected function retrieveLanguage(array $languages): string
    {
        foreach ($languages as $language) {
            if ($this->isWildcard($language->getTag())) {
                return $this->retrieveDefaultLanguage();
            }

            if ($this->isAppropriateLanguage($language)) {
                return $language->getTag();
            }
        }

        return $this->retrieveDefaultLanguage();
    }

    protected function isAppropriateLanguage(string $language): bool
    {
        $primarySubtag = $this->retrievePrimarySubtag($language);
        $primarySubtagLength = strlen($primarySubtag);

        if ($this->options['two_letter_only']) {
            return $primarySubtagLength === 2;
        }

        return $primarySubtagLength >= 2 && $primarySubtagLength <= 3;
    }

    protected function retrievePrimarySubtag(string $language): string
    {
        return explode($this->options['separator'], $language)[0];
    }

    protected function retrieveDefaultLanguage(): string
    {
        return $this->options['default_language'];
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
