<?php

namespace Kudashevs\AcceptLanguage\Converters;

use Kudashevs\AcceptLanguage\ValueObjects\Language;

interface AbstractToLanguageConverter
{
    /**
     * Perform a conversion process and return a Language instance.
     *
     * @param $data
     * @param float $quality
     * @return Language
     */
    public function convert($data, float $quality): Language;
}
