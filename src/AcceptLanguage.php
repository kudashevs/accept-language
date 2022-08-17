<?php

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use Kudashevs\AcceptLanguage\TagNormalizers\LanguageTagNormalizer;
use Kudashevs\AcceptLanguage\TagNormalizers\TagNormalizer;

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
     *  'http_accept_language' string A string with a custom HTTP Accept-Language header.
     *  'default_language' string A string with a default preferred language value.
     *  'accepted_languages' array An array with a list of supported languages.
     *  'two_letter_only' bool A boolean defines whether to use only the two-letter codes or not.
     *  'separator' string A string with a character that will be used as the separator in the result.
     * ]
     */
    private $options = [
        'http_accept_language' => '',
        'default_language' => 'en',
        'accepted_languages' => [],
        'two_letter_only' => true,
        'separator' => '_',
    ];

    /**
     * @var TagNormalizer
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
        $validated = $this->retrieveValidOptions($options);

        $this->options = array_merge($this->options, $validated);
    }

    /**
     * @param array $options
     * @return array
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

    /**
     * @return void
     */
    private function initNormalizer(): void
    {
        $this->normalizer = $this->createTagNormalizer();
    }

    /**
     * @return TagNormalizer
     */
    private function createTagNormalizer(): TagNormalizer
    {
        return new LanguageTagNormalizer([
            'separator' => $this->options['separator'],
        ]);
    }

    /**
     * Retrieves the HTTP Accept-Language header value, processes it
     * and set the $language state property for the future use.
     */
    protected function process(): void
    {
        $header = $this->retrieveAcceptLanguage();

        $this->language = $this->findLanguage($header);
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
     * @param string $header
     * @return string
     */
    protected function findLanguage(string $header): string
    {
        $languages = $this->parse($header);

        $filtered = $this->filter($languages);

        $normalized = $this->normalize($filtered);

        return $this->retrieveLanguage($normalized);
    }

    /**
     * Parses an HTTP Accept-Language string.
     *
     * @param string $header
     * @return array
     */
    private function parse(string $header): array
    {
        if (
            $this->isEmpty($header) ||
            $this->isWildcard($header)
        ) {
            return [];
        }

        return $this->parseHeader($header);
    }

    private function isEmpty(string $header): bool
    {
        return $header === '';
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
    private function parseHeader(string $headerValue): array
    {
        $emptyTagDefaultValue = 1;
        $tagKeys = ['lang', 'quality'];

        $result = [];
        foreach (explode(',', $headerValue) as $tag) {
            $splitTag = explode(';q=', trim($tag));

            $result[] = array_combine(
                $tagKeys,
                $this->normalizeHeaderTag($splitTag, $emptyTagDefaultValue)
            );

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
        $filtered = $this->excludeNotValidLanguages($languages);

        $filtered = $this->excludeNotInAcceptedLanguages($filtered);

        return $filtered;
    }

    /**
     * @param array $languages
     * @return array
     */
    private function excludeNotValidLanguages(array $languages): array
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
    private function excludeNotInAcceptedLanguages(array $filtered): array
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
        $normalized = array_map(function ($tag) {
            return [
                'lang' => $this->normalizeTag($tag['lang']),
                'quality' => $this->normalizeQuality($tag['quality']),
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
    public function getPreferredLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getPreferredLanguage();
    }
}
