<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Normalizers;

final class LanguageQualityNormalizer implements QualityNormalizerInterface
{
    /**
     * The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
     * and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
     */
    private const NOT_ACCEPTABLE_QUALITY = 0;

    /**
     * The constant defines whether empty quality values (empty "q" parameter values) are acceptable.
     */
    private const ALLOW_EMPTY_DEFAULT = true;

    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function normalize($quality, array $options = [])
    {
        if ($this->isUndefinedQuality($quality)) {
            return $this->generateForUndefined($options);
        }

        // Since some clients may omit the quality parameter (the value after "q=" in a request header field) and
        // this is not a serious violation, we might want to handle this empty value when a fallback is available.
        if ($this->isEmptyQuality($quality) && $this->isEmptyAllowed($options)) {
            return $this->generateForEmpty($options);
        }

        if ($this->isValidQuality($quality)) {
            return $this->generateForValid($quality);
        }

        return self::NOT_ACCEPTABLE_QUALITY;
    }

    /**
     * @param int|float|string|null $quality
     */
    private function isUndefinedQuality($quality): bool
    {
        return is_null($quality);
    }

    /**
     * @param array{fallback?: int|float} $options
     * @return int|float
     */
    private function generateForUndefined(array $options)
    {
        // If no "q" parameter is present, the default weight is 1. See RFC 7231, Section 5.3.1.
        $quality = 1;

        if (isset($options['fallback']) && $this->isValidQuality($options['fallback'])) {
            $quality = $options['fallback'];
        }

        return $this->prepareQuality($quality);
    }

    /**
     * @param int|float|string|null $quality
     */
    private function isValidQuality($quality): bool
    {
        // The weight is normalized to a real number in the range 0 through 1, where 0.001 is the least preferred
        // and 1 is the most preferred; a value of 0 means "not acceptable". See RFC 7231, Section 5.3.1.
        return is_numeric($quality)
            && $quality > 0
            && max(min($quality, 1), 0.001) === $quality;
    }

    /**
     * @param int|float|string|null $quality
     * @return int|float
     */
    private function generateForValid($quality)
    {
        return $this->prepareQuality($quality);
    }

    /**
     * @param int|float|string|null $quality
     */
    private function isEmptyQuality($quality): bool
    {
        return is_string($quality)
            && trim((string)$quality) === '';
    }

    /**
     * @param array{allow_empty?: bool} $options
     * @return bool
     */
    private function isEmptyAllowed(array $options): bool
    {
        if (isset($options['allow_empty'])) {
            return $options['allow_empty'] === true;
        }

        return self::ALLOW_EMPTY_DEFAULT;
    }

    /**
     * @param array{fallback?: int|float} $options
     * @return int|float
     */
    private function generateForEmpty(array $options)
    {
        $quality = self::NOT_ACCEPTABLE_QUALITY;

        if (isset($options['fallback']) && $this->isValidQuality($options['fallback'])) {
            $quality = $options['fallback'];
        }

        return $this->prepareQuality($quality);
    }

    /**
     * @param int|float|string|null $quality
     * @return int|float
     */
    private function prepareQuality($quality)
    {
        if ($quality === 0 || (int)$quality === 1) {
            return (int)$quality;
        }

        return (float)$quality;
    }
}
