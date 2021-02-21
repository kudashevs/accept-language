<?php

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;

class AcceptLanguage
{
    public const DEFAULT_LANGUAGE = 'en';

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
     *  'http_accept_language' string Defines the HTTP Accept-Language information string.
     *  'default_language' string Overrides the default language value.
     *  'accepted_languages' array Defines the supported languages.
     * ]
     */
    private $options = [
        'http_accept_language' => '',
        'default_language' => '',
        'accepted_languages' => [],
    ];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        $this->process();
    }

    protected function setOptions(array $options): void
    {
        $supportedOptions = array_intersect_key($options, $this->options);

        foreach ($supportedOptions as $key => $value) {
            if (gettype($value) !== gettype($this->options[$key])) {
                throw new InvalidOptionArgumentException('The option ' . $key . ' has a wrong value type ' . gettype($value) . '. Option requires a value of the type ' . gettype($this->options[$key]) . '.');
            }
        }

        $this->options = $supportedOptions;
    }

    /**
     * Retrieves the HTTP Accept-Language information and processes it.
     */
    protected function process(): void
    {
        $languageInformation = $this->retrieveAcceptLanguage();

        $this->language = $this->findLanguage($languageInformation);
    }

    /**
     * @return string
     */
    private function retrieveAcceptLanguage(): string
    {
        return trim($this->options['http_accept_language'] ?? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }

    /**
     * @param string $languageInformation
     * @return string
     */
    private function findLanguage(string $languageInformation): string
    {
        $languages = $this->parse($languageInformation);

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

        $languages = [];

        foreach (explode(',', $languageInformation) as $decoupledLangTag) {
            $splitTagAndQuality = array_pad(explode(';q=', trim($decoupledLangTag)), 2, 1);

            $languageTag = $this->normalizeTag($splitTagAndQuality[0]);
            $languageQuality = $this->normalizeQuality($splitTagAndQuality[1]);

            /**
             * The first registered language tag has the highest quality value.
             * All other similar tags will overwrite it and should be skipped.
             */
            if (array_key_exists($languageTag, $languages)) {
                continue;
            }

            $languages[$languageTag] = $languageQuality;
        }

        return $languages;
    }

    /**
     * @param string $languageInformation
     * @return bool
     */
    private function isSpecialRange(string $languageInformation): bool
    {
        if (
            $languageInformation === '*'
        ) {
            return true;
        }

        return false;
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

        $languages = $this->retrieveAcceptableLanguagesIntersection($languages);

        if (empty($languages)) {
            return $this->resolveDefaultLanguage();
        }

        $languages = $this->retrieveLanguagesSortedByQuality($languages);

        return $this->retrieveProperLanguage($languages);
    }

    /**
     * @param array $languages
     * @return array
     */
    private function retrieveAcceptableLanguagesIntersection(array $languages): array
    {
        if (!array_key_exists('accepted_languages', $this->options)) {
            return $languages;
        }

        return array_intersect_key($languages, array_flip($this->options['accepted_languages']));
    }

    /**
     * @param array $languages
     * @return array
     */
    private function retrieveLanguagesSortedByQuality(array $languages): array
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
            $length = strlen($language);
            if ($length >= 2 && $length <= 3) {
                return $language;
            }
        }

        return $this->resolveDefaultLanguage();
    }

    /**
     * @return string
     */
    private function resolveDefaultLanguage(): string
    {
        if ($this->canUseDefaultLanguageOption()) {
            return $this->options['default_language'];
        }

        return self::DEFAULT_LANGUAGE;
    }

    /**
     * @return bool
     */
    private function canUseDefaultLanguageOption(): bool
    {
        if (empty($this->options['default_language'])) {
            return false;
        }

        if (
            !empty($this->options['accepted_languages']) &&
            !in_array($this->options['default_language'], $this->options['accepted_languages'], true)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }
}
