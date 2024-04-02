<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Languages;

use Kudashevs\AcceptLanguage\Languages\DefaultLanguage;
use PHPUnit\Framework\TestCase;

class DefaultLanguageTest extends TestCase
{
    /** @test */
    public function it_can_create_a_valid_language(): void
    {
        $language = DefaultLanguage::create('en', 1);

        $this->assertNotEmpty($language->getTag());
        $this->assertNotEmpty($language->getQuality());
        $this->assertTrue($language->isValid());
    }

    /** @test */
    public function it_can_create_an_invalid_language_from_valid_data(): void
    {
        $language = DefaultLanguage::createInvalid('en', 1);

        $this->assertNotEmpty($language->getTag());
        $this->assertNotEmpty($language->getQuality());
        $this->assertFalse($language->isValid());
    }

    /** @test */
    public function it_can_create_an_invalid_language_from_an_invalid_type(): void
    {
        $language = DefaultLanguage::create(42, 1);

        $this->assertNotEmpty($language->getTag());
        $this->assertNotEmpty($language->getQuality());
        $this->assertFalse($language->isValid());
    }

    /** @test */
    public function it_can_create_an_invalid_language_from_an_invalid_language_string(): void
    {
        $language = DefaultLanguage::create('verywrong_language', 1);

        $this->assertNotEmpty($language->getTag());
        $this->assertNotEmpty($language->getQuality());
        $this->assertFalse($language->isValid());
    }

    /** @test */
    public function it_can_create_a_valid_language_when_quality_is_string(): void
    {
        $language = DefaultLanguage::create('en', '0.5');

        $this->assertSame('en', $language->getTag());
        $this->assertSame(0.5, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    /** @test */
    public function it_can_create_a_valid_language_when_quality_is_null(): void
    {
        $language = DefaultLanguage::create('en', null);

        $this->assertSame('en', $language->getTag());
        $this->assertSame(1, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentValidLanguageAndQualityValues
     */
    public function it_can_create_a_valid_language_from_the_valid_data(
        array $input,
        string $expectedTag,
        $expectedQuality
    ): void {
        $language = DefaultLanguage::create(...$input);

        $this->assertSame($expectedTag, $language->getTag());
        $this->assertSame($expectedQuality, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    public static function provideDifferentValidLanguageAndQualityValues(): array
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
    ): void {
        $language = DefaultLanguage::create(...$input);

        $this->assertSame($expectedTag, $language->getTag());
        $this->assertSame($expectedQuality, $language->getQuality());
        $this->assertFalse($language->isValid());
    }

    public static function provideDifferentInvalidLanguageAndQualityValues(): array
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

    /**
     * @test
     * @dataProvider provideDifferentLanguageAndQualityValuesWithDifferentOptions
     */
    public function it_can_create_a_valid_language_with_different_options(
        array $input,
        string $expectedTag,
        $expectedQuality
    ): void {
        $language = DefaultLanguage::create(...$input);

        $this->assertSame($expectedTag, $language->getTag());
        $this->assertSame($expectedQuality, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    public static function provideDifferentLanguageAndQualityValuesWithDifferentOptions(): array
    {
        return [
            'a language tag with all options disabled results in the expected language' => [
                [
                    'de-gsg-Latn-DE',
                    1,
                    [
                        'with_extlang' => false,
                        'with_script' => false,
                        'with_region' => false,
                    ],
                ],
                'de',
                1,
            ],
            'a language tag with all options enabled results in the expected language' => [
                [
                    'de-gsg-Latn-DE',
                    0,
                    [
                        'separator' => '~',
                        'with_extlang' => true,
                        'with_script' => true,
                        'with_region' => true,
                    ],
                ],
                'de~gsg~Latn~DE',
                0,
            ],
            'a quality with all options disabled results in the expected quality' => [
                [
                    'en',
                    '',
                    [
                        'allow_empty' => false,
                        'fallback_value' => 0.5,
                    ],
                ],
                'en',
                0,
            ],
            'a quality with all options enabled results in the expected quality' => [
                [
                    'en',
                    0.5,
                    [
                        'allow_empty' => true,
                        'fallback_value' => 0.5,
                    ],
                ],
                'en',
                0.5,
            ],
        ];
    }

    /** @test */
    public function it_can_retain_the_provided_options(): void
    {
        $language = DefaultLanguage::create('zh-yue-Hant-CN', 1, [
            'separator' => '~',
            'fallback_value' => 0.5,
        ]);

        $this->assertContains('~', $language->getOptions());
        $this->assertContains(0.5, $language->getOptions());
        $this->assertTrue($language->isValid());
    }

    /** @test */
    public function it_can_retrieve_subtags_of_a_tag(): void
    {
        $expectedSubtags = ['de', 'Latn', 'DE'];

        $language = DefaultLanguage::create('de-Latn-DE', 1);

        $this->assertCount(3, $language->getSubtags());
        $this->assertSame($expectedSubtags, $language->getSubtags());
        $this->assertTrue($language->isValid());
    }

    /** @test */
    public function it_can_retrieve_a_primary_subtag(): void
    {
        $language = DefaultLanguage::create('zh-yue-Hant-CN', 1);

        $this->assertSame('zh', $language->getPrimarySubtag());
        $this->assertTrue($language->isValid());
    }
}
