<?php

namespace Kudashevs\AcceptLanguage;

use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use Kudashevs\AcceptLanguage\TagNormalizers\LanguageTagNormalizer;
use Kudashevs\AcceptLanguage\TagNormalizers\TagNormalizer;

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

    protected TagNormalizer $normalizer;

    /**
     * @param array $options
     * @throws InvalidOptionArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->initOptions($options);
        $this->initNormalizer();
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

    protected function initNormalizer(): void
    {
        $this->normalizer = $this->createTagNormalizer();
    }

    protected function createTagNormalizer(): TagNormalizer
    {
        return new LanguageTagNormalizer([
            'separator' => $this->options['separator'],
        ]);
    }

    /**
     * Retrieve an HTTP Accept-Language header value, process it
     * and set the value of the $language field for the further use.
     *
     * @return void
     */
    public function process(): void
    {
        $header = $this->retrieveAcceptLanguage();

        $this->header = $header;
        $this->language = $this->findLanguage($header);
    }

    protected function retrieveAcceptLanguage(): string
    {
        $value = trim($this->options['http_accept_language']) === ''
            ? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']
            : $this->options['http_accept_language'];

        return trim($value);
    }

    protected function findLanguage(string $header): string
    {
        $languages = $this->parse($header);

        $filtered = $this->filter($languages);

        $normalized = $this->normalize($filtered);

        return $this->retrieveLanguage($normalized);
    }

    protected function parse(string $header): array
    {
        if (
            $this->isEmpty($header) ||
            $this->isWildcard($header)
        ) {
            return [];
        }

        return $this->parseHeader($header);
    }

    protected function isEmpty(string $header): bool
    {
        return $header === '';
    }

    protected function isWildcard(string $header): bool
    {
        return $header === '*';
    }

    protected function parseHeader(string $header): array
    {
        $defaultEmptyQualityValue = 1;

        /**
         * The Accept-Language header field consists of language-ranges. See RFC 7231, Section 5.3.5.
         * A basic language range differs from the language tags defined in only in that there is no requirement that
         * it be "well-formed" or be validated against the IANA Language Subtag Registry. See RFC 4647, Section 2.1.
         */
        $result = [];
        foreach (explode(',', $header) as $languageRange) {
            $splitLanguageRange = explode(';q=', trim($languageRange));

            $result[] = $this->prepareLanguageTag(
                $splitLanguageRange,
                $defaultEmptyQualityValue
            );

            $defaultEmptyQualityValue -= 0.1;
        }

        return $result;
    }

    protected function prepareLanguageTag(array $parts, float $quality): array
    {
        $predefinedLanguageTagKeys = ['lang', 'quality'];

        return array_combine(
            $predefinedLanguageTagKeys,
            $this->normalizeLanguageRange($parts, $quality)
        );
    }

    protected function normalizeLanguageRange(array $values, float $default): array
    {
        $expectedNumberOfElements = 2;
        $numberOfElements = count($values);

        if ($numberOfElements === $expectedNumberOfElements && is_numeric($values[1])) {
            return [$values[0], (float)$values[1]];
        }

        if ($numberOfElements === $expectedNumberOfElements - 1 || $values[1] === '') {
            return [$values[0], $default];
        }

        /**
         * It is better to return an empty tag when a language range is really malformed or something went really wrong.
         */
        return $this->generateEmptyArray($expectedNumberOfElements);
    }

    protected function generateEmptyArray(int $elements): array
    {
        return array_fill(0, $elements, '');
    }

    protected function filter(array $languages): array
    {
        $filtered = $this->excludeNotValidLanguages($languages);

        $filtered = $this->excludeNotInAcceptedLanguages($filtered);

        return $filtered;
    }

    protected function excludeNotValidLanguages(array $languages): array
    {
        return array_filter($languages, function ($value) {
            return $this->isValidLanguage($value['lang']) && $this->isValidQuality($value['quality']);
        });
    }

    protected function isValidLanguage($value): bool
    {
        return $this->isWildcard($value) || strlen($value) > 1;
    }

    protected function isValidQuality($value): bool
    {
        return !empty($value) &&
            is_numeric($value) &&
            max(min($value, 1), 0) === $value;
    }

    protected function excludeNotInAcceptedLanguages(array $filtered): array
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

    protected function prepareAcceptedLanguagesForCompare(): array
    {
        return array_map(function ($value) {
            return $this->prepareLanguageForCompare($value);
        }, $this->options['accepted_languages']);
    }

    protected function prepareLanguageForCompare(string $language): string
    {
        return strtolower(str_replace('_', '-', $language));
    }

    protected function normalize(array $languages): array
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

    protected function normalizeTag(string $tag): string
    {
        return $this->normalizer->normalize($tag);
    }

    protected function normalizeQuality(string $quality): float
    {
        return (float)$quality;
    }

    protected function retrieveLanguage(array $languages): string
    {
        if (count($languages) === 0) {
            return $this->retrieveDefaultLanguage();
        }

        return $this->retrieveProperLanguage($languages);
    }

    protected function retrieveProperLanguage(array $languages): string
    {
        foreach (array_column($languages, 'lang') as $language) {
            if ($this->isWildcard($language)) {
                return $this->retrieveDefaultLanguage();
            }

            if ($this->isProperLanguage($language)) {
                return $language;
            }
        }

        return $this->retrieveDefaultLanguage();
    }

    protected function isProperLanguage(string $language): bool
    {
        $primarySubtag = explode($this->options['separator'], $language)[0];
        $primaryLength = strlen($primarySubtag);

        return $this->isProperPrimarySubtag($primaryLength);
    }

    protected function isProperPrimarySubtag(int $length): bool
    {
        if ($this->options['two_letter_only']) {
            return $length === 2;
        }

        return $length >= 2 && $length <= 3;
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
