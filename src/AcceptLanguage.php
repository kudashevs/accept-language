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
     * Contains various options if any.
     *
     * @var array
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

    private function setOptions(array $options): void
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

        $languages = $this->parseLanguageInformation($languageInformation);

        return $this->retrieveLanguage($languages);
    }

    /**
     * Parses an HTTP Accept-Language string.
     *
     * @param string $languageInformation
     * @return array
     */
    private function parseLanguageInformation(string $languageInformation): array
    {
        $languages = [];

        foreach (explode(',', $languageInformation) as $decoupledLangTag) {
            $splitLangTagAndQuality = array_pad(explode(';q=', trim($decoupledLangTag)), 2, 1);

            $langTag = $this->normalizeTag($splitLangTagAndQuality[0]);
            $langQuality = $this->normalizeQuality($splitLangTagAndQuality[1]);

            /**
             * The first registered language tag has the highest quality value.
             * All other similar tags will overwrite it and should be skipped.
             */
            if (array_key_exists($langTag, $languages)) {
                continue;
            }

            $languages[$langTag] = $langQuality;
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
        $languages = $this->retrieveAcceptableLanguagesIntersection($languages);

        if (empty($languages)) {
            return $this->resolveDefaultLanguage();
        }

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
     * @return string
     */
    private function retrieveProperLanguage(array $languages): string
    {
        $highestQualityLanguages = array_keys($languages, max($languages));

        return $this->findProperLanguage($highestQualityLanguages);
    }

    /**
     * @param array $languages
     * @return string
     */
    private function findProperLanguage(array $languages): string
    {
        foreach ($languages as $language) {
            if (strlen($language) >= 2) {
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
