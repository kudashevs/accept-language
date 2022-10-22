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
     * Retrieve an HTTP Accept-Language header, process its value, find
     * a language of preference, and update the state for further use.
     *
     * @return void
     */
    public function process(): void
    {
        $header = $this->retrieveAcceptLanguageHeader();

        $this->header = $header;
        $this->language = $this->findPreferredLanguage($header);
    }

    protected function retrieveAcceptLanguageHeader(): string
    {
        $value = trim($this->options['http_accept_language']) === ''
            ? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE']
            : $this->options['http_accept_language'];

        return trim($value);
    }

    protected function findPreferredLanguage(string $header): string
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
        /**
         * The Accept-Language header field consists of language-ranges. See RFC 7231, Section 5.3.5.
         * A basic language range differs from the language tags defined in only in that there is no requirement that
         * it be "well-formed" or be validated against the IANA Language Subtag Registry. See RFC 4647, Section 2.1.
         */
        $ranges = $this->prepareLanguageRanges($header);

        $result = $this->excludeInvalidLanguageRanges($ranges);

        return $result;
    }

    protected function prepareLanguageRanges(string $header): array
    {
        $defaultEmptyQualityValue = 1;

        $ranges = [];
        foreach (explode(',', $header) as $languageRange) {
            $splitLanguageRange = explode(';q=', trim($languageRange));

            $ranges[] = $this->prepareLanguageRange(
                $splitLanguageRange,
                $defaultEmptyQualityValue
            );

            $defaultEmptyQualityValue -= 0.1;
        }

        return $ranges;
    }

    protected function prepareLanguageRange(array $parts, float $quality): array
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

        /**
         * A proper language range with a quality value.
         */
        if ($numberOfElements === $expectedNumberOfElements && is_numeric($values[1])) {
            return $this->normalizeLanguageRangeWithQuality($values);
        }

        /**
         * A proper language range with an empty quality value.
         */
        if ($numberOfElements === $expectedNumberOfElements && trim($values[1]) === '') {
            return $this->normalizeLanguageRangeWithoutQuality($values, $default);
        }

        /**
         * A proper language range without a quality value.
         */
        if ($numberOfElements === $expectedNumberOfElements - 1) {
            return $this->normalizeLanguageRangeWithoutQuality($values, $default);
        }

        /**
         * It is better to return an empty tag when a language range is really malformed or something went really wrong.
         */
        return $this->generateEmptyLanguageRange();
    }

    /**
     * @return array<string,float>
     */
    protected function normalizeLanguageRangeWithQuality(array $values): array
    {
        return [$values[0], (float)$values[1]];
    }

    /**
     * @return array<string,float>
     */
    protected function normalizeLanguageRangeWithoutQuality(array $values, float $quality): array
    {
        return [$values[0], $quality];
    }

    /**
     * @return array<string,float>
     */
    protected function generateEmptyLanguageRange(): array
    {
        return ['', 0];
    }

    protected function excludeInvalidLanguageRanges(array $ranges): array
    {
        return array_filter($ranges, function ($value) {
            return $this->isValidLanguage($value['lang']) && $this->isValidQuality($value['quality']);
        });
    }

    protected function isValidLanguage($value): bool
    {
        return $this->isWildcard($value) || strlen($value) > 1;
    }

    protected function isValidQuality($value): bool
    {
        /**
         * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
         * and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
         */
        return is_numeric($value) &&
            $value > 0 &&
            max(min($value, 1), 0.001) === $value;
    }

    protected function filter(array $languages): array
    {
        return $this->excludeNotAcceptedLanguages($languages);
    }

    protected function excludeNotAcceptedLanguages(array $languages): array
    {
        if (count($this->options['accepted_languages']) === 0) {
            return $languages;
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
