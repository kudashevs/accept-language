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
     *  'separator' string A string with a character that will be used as the separator.
     *  'accepted_languages' array An array with a list of supported languages.
     * ]
     */
    private $options = [
        'http_accept_language' => '',
        'default_language' => 'en',
        'separator' => '_',
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
            $blankTag = ['lang', 'quality'];
            $splitTag = array_pad(explode(';q=', trim($tag)), 2, 1);

            return array_combine($blankTag, $splitTag);
        }, explode(',', $headerValue));
    }

    /**
     * @param array $languages
     * @return array
     */
    private function filter(array $languages): array
    {
        $filtered = array_filter($languages, function ($value) {
            return $this->isValidLanguage($value['lang']) && $this->isValidQuality($value['quality']);
        });

        if (empty($this->options['accepted_languages'])) {
            return $filtered;
        }

        $accepted = $this->prepareAcceptedLanguagesOption();

        return array_filter($filtered, function ($value) use ($accepted) {
            return in_array($this->prepareLanguageForCompare($value['lang']), $accepted, true);
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
        return !empty($value) &&
            is_numeric($value) &&
            max(min($value, 1), 0) === $value;
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

    /**
     * @param array $languages
     * @return array
     */
    private function normalize(array $languages): array
    {
        $normalized = array_map(function ($value) {
            return [
                'lang' => $this->normalizeTag($value['lang']),
                'quality' => $this->normalizeQuality($value['quality']),
            ];
        }, $languages);

        usort($normalized, function($a, $b) {
            return $b['quality'] <=> $a['quality'];
        });

        return $normalized;
    }

    /**
     * @param string $tag
     * @return string
     */
    private function normalizeTag(string $tag): string
    {
        return $this->normalizer->normalize($tag, $this->options['separator']);
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
        foreach (array_column($languages, 'lang') as $language) {
            if ($this->isSpecialRange($language)) {
                break;
            }

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

        if (strpos($language, $this->options['separator']) === false) {
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
