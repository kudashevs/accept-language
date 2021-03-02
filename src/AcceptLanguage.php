<?php

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use Kudashevs\AcceptLanguage\Support\LanguageTagNormalizer;

class AcceptLanguage
{
    /**
     * Contains the found language.
     *
     * @var string
     */
    private $language;

    /**
     * @var LanguageTagNormalizer
     */
    private $normalizer;

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
        $this->normalizer = new LanguageTagNormalizer();
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

        $filtered = $this->filter($languages);

        $normalized = $this->normalize($filtered);

        return $this->retrieveLanguage($normalized);
    }

    /**
     * Parses an HTTP Accept-Language string.
     *
     * @param string $headerValue
     * @return array
     */
    private function parse(string $headerValue): array
    {
        if (
            empty($headerValue) ||
            $this->isSpecialRange($headerValue)
        ) {
            return [];
        }

        return $this->parseHeaderValue($headerValue);
    }

    /**
     * @param string $headerValue
     * @return bool
     */
    private function isSpecialRange(string $headerValue): bool
    {
        return $headerValue === '*';
    }

    /**
     * @param string $headerValue
     * @return array
     */
    private function parseHeaderValue(string $headerValue): array
    {
        return array_map(function ($tag) {
            return array_pad(explode(';q=', trim($tag)), 2, 1);
        }, explode(',', $headerValue));
    }

    /**
     * @param array $languages
     * @return array
     */
    private function filter(array $languages): array
    {
        $filtered = array_filter($languages, function ($value) {
            return $this->isValidLanguage($value[0]) && $this->isValidQuality($value[1]);
        });

        if (empty($this->options['accepted_languages'])) {
            return $filtered;
        }

        $accepted = $this->prepareAcceptedLanguagesOption();

        return array_filter($filtered, function ($value) use ($accepted) {
            return in_array($this->prepareLanguageForCompare($value[0]), $accepted, true);
        });
    }

    /**
     * @param $value
     * @return bool
     */
    private function isValidLanguage($value): bool
    {
        return !empty($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function isValidQuality($value): bool
    {
        return !empty($value) && is_numeric($value) && (bool)filter_var($value, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.1, 'max_range' => 1]]);
    }

    /**
     * @return array
     */
    private function prepareAcceptedLanguagesOption(): array
    {
        return array_map(function ($value) {
            return $this->prepareLanguageForCompare($value);
        }, $this->options['accepted_languages']);
    }

    /**
     * @param string $language
     * @return string
     */
    private function prepareLanguageForCompare(string $language): string
    {
        return strtolower(str_replace('_', '-', $language));
    }

    private function normalize(array $languages): array
    {
        $normalized = array_map(function ($value) {
            return [
                $this->normalizeTag($value[0]),
                $this->normalizeQuality($value[1]),
            ];
        }, $languages);

        //sorting
        return $normalized;
    }

    /**
     * @param string $tag
     * @return string
     */
    private function normalizeTag(string $tag): string
    {
        return $this->normalizer->normalize($tag);
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

        return $this->retrieveProperLanguage($languages);
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
        $length = strlen($language);

        if (strpos($language,'-') === false) {
            return $length >= 2 && $length <= 3;
        }

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
