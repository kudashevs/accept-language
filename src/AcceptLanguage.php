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
     * @var array[
     *  'http_accept_language' string A string with custom HTTP Accept-Language header.
     *  'default_language' string A string with a default preferred language value.
     *  'accepted_languages' array An array with a list of supported languages.
     *  'two_letter_only' bool A boolean defines whether to use the two-letter codes only or not.
     *  'separator' string A string with a character that will be used as the separator.
     * ]
     */
    private $options = [
        'http_accept_language' => '',
        'default_language' => 'en',
        'two_letter_only' => true,
        'separator' => '_',
        'accepted_languages' => [],
    ];

    /**
     * @var LanguageTagNormalizer
     */
    private $normalizer;

    /**
     * @param array $options
     * @throws InvalidOptionArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
        $this->initNormalizer();

        $this->process();
    }

    /**
     * @param array $options
     * @throws InvalidOptionArgumentException
     */
    protected function initOptions(array $options): void
    {
        $validated = $this->validateOptions($options);

        $this->options = array_merge($this->options, $validated);
    }

    /**
     * @param array $options
     * @return array
     * @throws InvalidOptionArgumentException
     */
    protected function validateOptions(array $options): array
    {
        $allowedOptions = array_intersect_key($options, $this->options);

        foreach ($allowedOptions as $key => $value) {
            if (gettype($this->options[$key]) !== gettype($value)) {
                throw new InvalidOptionArgumentException('The option ' . $key . ' has a wrong value type ' . gettype($value) . '. Option requires a value of the type ' . gettype($this->options[$key]) . '.');
            }
        }

        return $allowedOptions;
    }

    /**
     * @return void
     */
    private function initNormalizer(): void
    {
        $this->normalizer = new LanguageTagNormalizer();
    }

    /**
     * Retrieves the HTTP Accept-Language header value, processes it
     * and set the $language state property for the future use.
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
            $this->isWildcard($headerValue)
        ) {
            return [];
        }

        return $this->parseHeaderValue($headerValue);
    }

    /**
     * @param string $headerValue
     * @return bool
     */
    private function isWildcard(string $headerValue): bool
    {
        return $headerValue === '*';
    }

    /**
     * @param string $headerValue
     * @return array
     */
    private function parseHeaderValue(string $headerValue): array
    {
        $result = [];
        $emptyTagDefaultValue = 1;
        $tagKeys = ['lang', 'quality'];

        foreach (explode(',', $headerValue) as $tag) {
            $splitTag = explode(';q=', trim($tag));
            $tagValues = $this->normalizeHeaderTag($splitTag, $emptyTagDefaultValue);

            if (!empty($tagValues)) {
                $result[] = array_combine($tagKeys, $tagValues);
            }

            $emptyTagDefaultValue -= 0.1;
        }

        return $result;
    }

    /**
     * @param array $values
     * @param float $default
     * @return array
     */
    private function normalizeHeaderTag(array $values, float $default): array
    {
        $expectedElementsNumber = 2;

        if (count($values) > $expectedElementsNumber) {
            return array_fill(0, $expectedElementsNumber, '');
        }

        if (count($values) !== $expectedElementsNumber || ($values[1] === '')) {
            $values[1] = $default;
        }

        return $values;
    }

    /**
     * @param array $languages
     * @return array
     */
    private function filter(array $languages): array
    {
        $filtered = $this->filterLanguages($languages);

        $filtered = $this->filterLanguagesThroughAcceptedLanguages($filtered);

        return $filtered;
    }

    /**
     * @param array $languages
     * @return array
     */
    private function filterLanguages(array $languages): array
    {
        return array_filter($languages, function ($value) {
            return $this->isValidLanguage($value['lang']) && $this->isValidQuality($value['quality']);
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
     * @param array $filtered
     * @return array
     */
    private function filterLanguagesThroughAcceptedLanguages(array $filtered): array
    {
        if (empty($this->options['accepted_languages'])) {
            return $filtered;
        }

        $accepted = $this->prepareAcceptedLanguagesForCompare();

        $filtered = array_filter($filtered, function ($value) use ($accepted) {
            $language = $this->prepareLanguageForCompare($value['lang']);

            return in_array($language, $accepted, true);
        });

        return $filtered;
    }

    /**
     * @return array
     */
    private function prepareAcceptedLanguagesForCompare(): array
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

        usort($normalized, static function ($a, $b) {
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
        return str_replace('-', $this->options['separator'], $this->normalizer->normalize($tag));
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
            if ($this->isWildcard($language)) {
                return $this->resolveDefaultLanguage();
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
        $primarySubtag = explode($this->options['separator'], $language)[0];
        $primaryLength = strlen($primarySubtag);

        return $this->isProperPrimarySubtag($primaryLength);
    }

    private function isProperPrimarySubtag(int $length): bool
    {
        if ($this->options['two_letter_only']) {
            return $length === 2;
        }

        return $length >= 2 && $length <= 3;
    }

    /**
     * @return string
     */
    private function resolveDefaultLanguage(): string
    {
        return $this->options['default_language'];
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getPreferredLanguage();
    }

    /**
     * @return string
     */
    public function getPreferredLanguage(): string
    {
        return $this->language;
    }
}
