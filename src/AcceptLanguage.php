<?php

namespace Kudashevs\AcceptLanguage;

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
     * Contains various options if any.
     *
     * @var array
     */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;

        $this->process();
    }

    /**
     * Processes HTTP Accept-Language information.
     */
    public function process(): void
    {
        $languageInformation = $this->retrieveAcceptLanguage();

        $this->language = $this->parse($languageInformation);
    }

    /**
     * @return string
     */
    private function retrieveAcceptLanguage(): string
    {
        return trim($this->options['http_accept_language'] ?? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }

    /**
     * Parse HTTP Accept-Language string.
     *
     * @param string $languageInformation
     * @return string
     */
    private function parse(string $languageInformation): string
    {
        if (empty($languageInformation) || $languageInformation === '*') {
            return $this->resolveDefaultLanguage();
        }

        $languages = [];
        foreach (explode(',', $languageInformation) as $rawLanguageTag) {
            $splitTagAndQuality = array_pad(explode(';q=', trim($rawLanguageTag)), 2, 1);

            $langTag = $this->normalizeTag($splitTagAndQuality[0]);
            $langQuality = $this->normalizeQuality($splitTagAndQuality[1]);

            $languages[$langTag] = $langQuality;
        }

        return $this->retrieveLanguage($languages);
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
        $languages = $this->retrieveAcceptableLanguagesIntersection($languages);

        if (empty($languages)) {
            return $this->resolveDefaultLanguage();
        }

        return array_search(max($languages), $languages, true);
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

        if (!is_array($this->options['accepted_languages'])) {
            return [];
        }

        return array_intersect_key(array_flip($this->options['accepted_languages']), $languages);
    }

    /**
     * @return string
     */
    private function resolveDefaultLanguage(): string
    {
        if (!empty($this->options['default_language'])) { // todo add check with supported languages
            return $this->options['default_language'];
        }

        return self::DEFAULT_LANGUAGE;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }
}
