<?php

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;

class AcceptLanguage
{
    /**
     * Contains the found language.
     *
     * @var string
     */
    private $language;

    /**
     * Contains various options.
     *
     * @var array[
     *  'http_accept_language' string A string with custom HTTP Accept-Language information.
     *  'default_language' string A string with a default preferred language value.
     *  'accepted_languages' array An array with a list of supported languages.
     * ]
     */
    private $options = [
        'http_accept_language' => '',
        'default_language' => 'en',
        'accepted_languages' => [],
    ];

    /**
     * @param array $options
     * @throws InvalidOptionArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        $this->process();
    }

    /**
     * @param array $options
     * @throws InvalidOptionArgumentException
     */
    protected function setOptions(array $options): void
    {
        $matchingOptions = array_intersect_key($options, $this->options);

        foreach ($matchingOptions as $key => $value) {
            if (gettype($value) !== gettype($this->options[$key])) {
                throw new InvalidOptionArgumentException('The option ' . $key . ' has a wrong value type ' . gettype($value) . '. Option requires a value of the type ' . gettype($this->options[$key]) . '.');
            }
        }

        $this->options = array_merge($this->options, $matchingOptions);
    }

    /**
     * Retrieves the HTTP Accept-Language header value, processes it
     * and set the $language state property for further use.
     */
    protected function process(): void
    {
        $headerValue = $this->retrieveAcceptLanguage();

        $this->language = $this->findLanguage($headerValue);
    }

    /**
     * @return string
     */
    private function retrieveAcceptLanguage(): string
    {
        $value = empty($this->options['http_accept_language'])
            ? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']
            : $this->options['http_accept_language'];

        return trim($value);
    }

    /**
     * @param string $headerValue
     * @return string
     */
    private function findLanguage(string $headerValue): string
    {
        $languages = $this->parse($headerValue);

        return $this->retrieveLanguage($languages);
    }

    /**
     * Parses an HTTP Accept-Language string.
     *
     * @param string $languageInformation
     * @return array
     */
    private function parse(string $languageInformation): array
    {
        if ($this->isSpecialRange($languageInformation)) {
            return [];
        }

        return $this->parseHeaderValue($languageInformation);
    }

    /**
     * @param string $languageInformation
     * @return bool
     */
    private function isSpecialRange(string $languageInformation): bool
    {
        return $languageInformation === '*';
    }

    /**
     * @param string $headerValue
     * @return array
     */
    private function parseHeaderValue(string $headerValue): array
    {
        $languages = [];

        foreach (explode(',', $headerValue) as $separateLanguageTag) {
            $splitTagAndQuality = array_pad(explode(';q=', trim($separateLanguageTag)), 2, 1);

            $languageValue = $this->normalizeTag($splitTagAndQuality[0]);
            $languageQuality = $this->normalizeQuality($splitTagAndQuality[1]);

            /**
             * The first registered language tag has the highest quality value.
             * All other similar tags will overwrite it and should be skipped.
             */
            if (array_key_exists($languageValue, $languages)) {
                continue;
            }

            $languages[$languageValue] = $languageQuality;
        }

        return $languages;
    }

    /**
     * @param string $tag
     * @return string
     */
    private function normalizeTag(string $tag): string
    {
        return explode('-', $tag)[0];
    }

    /**
     * @param string $quality
     * @return float
     */
    private function normalizeQuality(string $quality): float
    {
        return (float)$quality;
    }

    /**
     * @param array $languages
     * @return string
     */
    private function retrieveLanguage(array $languages): string
    {
        if (empty($languages)) {
            return $this->resolveDefaultLanguage();
        }

        $languages = $this->retrieveIntersectionWithAcceptableLanguages($languages);

        if (empty($languages)) {
            return $this->resolveDefaultLanguage();
        }

        $languages = $this->retrieveLanguagesWithHighestQuality($languages);

        return $this->retrieveProperLanguage($languages);
    }

    /**
     * @param array $languages
     * @return array
     */
    private function retrieveIntersectionWithAcceptableLanguages(array $languages): array
    {
        if (empty($this->options['accepted_languages'])) {
            return $languages;
        }

        return array_intersect_key($languages, array_flip($this->options['accepted_languages']));
    }

    /**
     * @param array $languages
     * @return array
     */
    private function retrieveLanguagesWithHighestQuality(array $languages): array
    {
        return array_keys($languages, max($languages));
    }

    /**
     * @param array $languages
     * @return string
     */
    private function retrieveProperLanguage(array $languages): string
    {
        foreach ($languages as $language) {
            if ($this->isProperLanguage($language)) {
                return $language;
            }
        }

        return $this->resolveDefaultLanguage();
    }

    /**
     * @param string $language
     * @return bool
     */
    private function isProperLanguage(string $language): bool
    {
        return strlen($language) >= 2;
    }

    /**
     * @return string
     */
    private function resolveDefaultLanguage(): string
    {
        return $this->options['default_language'];
    }

    /**
     * @deprecated 2.0.0 The name does not provide the real meaning.
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getPreferredLanguage(): string
    {
        return $this->language;
    }
}
