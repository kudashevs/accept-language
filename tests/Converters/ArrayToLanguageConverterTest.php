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
     * @dataProvider provideDifferentDataWithInvalidValues
     */
    public function it_can_convert_the_invalid_data_to_an_invalid_language(
        array $data,
        string $expectedTag,
        float $expectedQuality
    ) {
        $service = new ArrayToLanguageConverter();
        $language = $service->convert($data, 1);

        $this->assertEquals($expectedTag, $language->getTag());
        $this->assertEquals($expectedQuality, $language->getQuality());
        $this->assertFalse($language->isValid());
    }

    public function provideDifferentDataWithInvalidValues(): array
    {
        return [
            'an array with too many values results in an invalid language' => [
                ['en', '42', 0],
                'en',
                42,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentDataWithValidValues
     */
    public function it_can_convert_the_valid_data_to_a_language(
        array $data,
        string $expectedTag,
        float $expectedQuality
    ) {
        $service = new ArrayToLanguageConverter();
        $language = $service->convert(...$data);

        $this->assertEquals($expectedTag, $language->getTag());
        $this->assertEquals($expectedQuality, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    public function provideDifferentDataWithValidValues(): array
    {
        return [
            'a valid language without a quality results in a language with fallback' => [
                [['en'], 0.5],
                'en',
                0.5,
            ],
            'a valid language with a empty quality results in a language with fallback' => [
                [['en', ''], 0.5],
                'en',
                0.5,
            ],
            'a valid language with a valid quality results in a language' => [
                [['en', 0.5], 1],
                'en',
                0.5,
            ],
        ];
    }
}
