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

            $languages[(string)$splitTagAndQuality[0]] = (float)$splitTagAndQuality[1];
        }

        return $this->retrieveLanguage($languages);
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

        $language = array_search(max($languages), $languages, true);
        $language = $this->trimLanguageTag($language);

        if ($this->isAcceptableLanguage($language)) {
            return $language;
        }

        return $this->resolveDefaultLanguage();
    }

    /**
     * @param string $language
     * @return bool
     */
    private function isAcceptableLanguage(string $language)
    {
        /**
         * Any language is accepted when 'accepted_languages' is not defined.
         */
        if (!array_key_exists('accepted_languages', $this->options)) {
            return true;
        }

        /**
         * Better silently return default value when 'accepted_languages' is wrong.
         */
        if (!is_array($this->options['accepted_languages'])) {
            return false;
        }

        return in_array($language, $this->options['accepted_languages'], true);
    }

    /**
     * @param string $tag
     * @return string
     */
    private function trimLanguageTag(string $tag): string
    {
        return explode('-', $tag)[0];
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
