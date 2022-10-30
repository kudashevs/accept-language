<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Converters;

use Kudashevs\AcceptLanguage\Exceptions\InvalidConverterArgumentException;
use Kudashevs\AcceptLanguage\ValueObjects\Language;

class ArrayToLanguageConverter implements AbstractToLanguageConverter
{
    protected const EXPECTED_ARRAY_SIZE = 2;

    public function __construct()
    {
    }

    /**
     * @param array $data
     * @param float $quality
     * @return Language
     */
    public function convert($data, float $quality): Language
    {
        $this->checkValidDataSource($data);

        return $this->convertArrayToLanguage($data, $quality);
    }

    protected function checkValidDataSource($data): void
    {
        if (!is_array($data)) {
            throw new InvalidConverterArgumentException('An argument of a wrong type. An array is expected.');
        }

        if (count($data) === 0) {
            throw new InvalidConverterArgumentException('Cannot process an empty array.');
        }
    }

    public function convertArrayToLanguage(array $data, float $fallbackQuality): Language
    {
        if (count($data) > self::EXPECTED_ARRAY_SIZE) {
            return $this->makeLanguage($data[0], $data[1]);
        }

        $quality = (!isset($data[1]) || $data[1] === '') ? $fallbackQuality : $data[1];

        return $this->makeLanguage($data[0], $quality);
    }

    /**
     * @param string $tag
     * @param int|float $quality
     * @return Language
     */
    public function makeLanguage(string $tag, $quality): Language
    {
        return Language::create($tag, $quality);
    }
}
