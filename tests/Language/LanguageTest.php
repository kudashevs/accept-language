<?php

namespace Kudashevs\AcceptLanguage\Tests\Language;

use Kudashevs\AcceptLanguage\Language\Language;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    /** @test */
    public function it_can_create_a_language()
    {
        $language = Language::create('en', 1);

        $this->assertNotEmpty($language->getTag());
        $this->assertNotEmpty($language->getQuality());
        $this->assertTrue($language->isValid());
    }

    /** @test */
    public function it_can_create_an_invalid_language_from_valid_data()
    {
        $language = Language::createInvalid('en', 1);

        $this->assertNotEmpty($language->getTag());
        $this->assertNotEmpty($language->getQuality());
        $this->assertFalse($language->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentValidLanguageAndQualityValues
     */
    public function it_can_create_a_valid_language_from_the_valid_data(
        array $input,
        string $expectedTag,
        $expectedQuality
    ) {
        $language = Language::create(...$input);

        $this->assertSame($expectedTag, $language->getTag());
        $this->assertSame($expectedQuality, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    public function provideDifferentValidLanguageAndQualityValues(): array
    {
        return [
            'a language tag and a minimum quality results in a valid language' => [
                ['en', 0],
                'en',
                0,
            ],
            'a language tag and a valid quality results in a valid language' => [
                ['en', 0.5],
                'en',
                0.5,
            ],
            'a language tag and a maximum quality results in a valid language' => [
                ['en', 1],
                'en',
                1,
            ],
            'a language tag with region subtag and a valid quality results in a valid language' => [
                ['de-DE', 1],
                'de-DE',
                1,
            ],
            'a language tag with script subtag and a valid quality results in a valid language' => [
                ['de-Latn', 1],
                'de-Latn',
                1,
            ],
            'a language tag with extlang, script, and region subtags and a valid quality results in a valid language' => [
                ['de-ger-Latn-DE', 1],
                'de-Latn-DE',
                1,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentInvalidLanguageAndQualityValues
     */
    public function it_can_create_an_invalid_language_from_the_invalid_data(
        array $input,
        string $expectedTag,
        $expectedQuality
    ) {
        $language = Language::create(...$input);

        $this->assertSame($expectedTag, $language->getTag());
        $this->assertSame($expectedQuality, $language->getQuality());
        $this->assertFalse($language->isValid());
    }

    public function provideDifferentInvalidLanguageAndQualityValues(): array
    {
        return [
            'an empty language tag and a valid quality results in a non valid language' => [
                ['', 0],
                '',
                0,
            ],
            'a language tag with space and a valid quality results in a non valid language' => [
                [' ', 0],
                ' ',
                0,
            ],
            'a language tag with space in text and a valid quality results in a non valid language' => [
                ['not valid', 0],
                'not valid',
                0,
            ],
            'a language tag with tabulation in text and a valid quality results in a non valid language' => [
                ["not\tvalid", 0],
                "not\tvalid",
                0,
            ],
            'a valid language tag with a negative quality results in a non valid language' => [
                ['en', -1],
                'en',
                -1,
            ],
            'a valid language tag with a quality lesser than minimum results in a non valid language' => [
                ['en', -0.001],
                'en',
                -0.001,
            ],
            'a valid language tag with a quality greater than maximum results in a non valid language' => [
                ['en', 1.001],
                'en',
                1.001,
            ],
        ];
    }
}
