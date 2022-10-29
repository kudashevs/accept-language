<?php

namespace Kudashevs\AcceptLanguage\Tests\Converters;

use Kudashevs\AcceptLanguage\Converters\ArrayToLanguageConverter;
use Kudashevs\AcceptLanguage\Exceptions\InvalidConverterArgumentException;
use PHPUnit\Framework\TestCase;

class ArrayToLanguageConverterTest extends TestCase
{
    /** @test */
    public function it_can_throw_exception_when_data_is_of_a_wrong_type()
    {
        $this->expectException(InvalidConverterArgumentException::class);
        $this->expectExceptionMessage('wrong');

        $service = new ArrayToLanguageConverter();
        $service->convert(new \stdClass(), 1);
    }

    /** @test */
    public function it_can_throw_exception_when_an_empty_array_is_provided()
    {
        $this->expectException(InvalidConverterArgumentException::class);
        $this->expectExceptionMessage('empty');

        $service = new ArrayToLanguageConverter();
        $service->convert([], 1);
    }

    /**
     * @test
     * @dataProvider provideDifferentRawLanguageRanges
     */
    public function it_can_convert_a_raw_language_range_to_a_language(array $rawLanguageRange, float $quality)
    {
        $service = new ArrayToLanguageConverter();
        $language = $service->convert($rawLanguageRange, $quality);

        $this->assertTrue($language->isValid());
    }

    public function provideDifferentRawLanguageRanges(): array
    {
        return [
            'an empty language range results in invalid language' => [
                ['en', '42', 0],
                1,
            ],
            'a valid language range results in a language' => [
                ['en', 0.5],
                1,
            ],

        ];
    }
}
