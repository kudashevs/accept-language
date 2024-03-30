<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\Factories;

use Kudashevs\AcceptLanguage\Factories\LanguageFactory;
use PHPUnit\Framework\TestCase;

class LanguageFactoryTest extends TestCase
{
    /** @test */
    public function it_can_hande_when_language_range_is_empty()
    {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageRange([], 1);

        $this->assertFalse($language->isValid());
    }

    /** @test */
    public function it_can_handle_when_language_range_is_suspicious()
    {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageRange(['en', 1, 'wrong', 42], 1);

        $this->assertFalse($language->isValid());
    }

    /** @test */
    public function it_can_create_a_language_tag_from_a_valid_language_string()
    {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageString('en-US');

        $this->assertSame('en-US', $language->getTag());
        $this->assertSame(1, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    /** @test */
    public function it_can_create_a_language_from_an_invalid_language_string()
    {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageString('verywrong');

        $this->assertSame('verywrong', $language->getTag());
        $this->assertSame(1, $language->getQuality());
        $this->assertFalse($language->isValid());
    }

    /** @test */
    public function it_can_create_a_language_tag_from_a_language_string_and_a_valid_quality()
    {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageString('en-US', 0.8);

        $this->assertSame('en-US', $language->getTag());
        $this->assertSame(0.8, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    /** @test */
    public function it_can_create_a_language_tag_from_a_language_range()
    {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageRange(['en'], 1);

        $this->assertSame('en', $language->getTag());
        $this->assertSame(1, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentInvalidLanguageRanges
     */
    public function it_can_create_from_the_invalid_language_range_an_invalid_language(
        array $range,
        string $expectedTag,
        float $expectedQuality
    ) {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageRange($range, 1);

        $this->assertEquals($expectedTag, $language->getTag());
        $this->assertEquals($expectedQuality, $language->getQuality());
        $this->assertFalse($language->isValid());
    }

    public static function provideDifferentInvalidLanguageRanges(): array
    {
        return [
            'a language range with too many values results in an invalid language' => [
                ['en', '42', 0],
                'en',
                42,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentValidLanguageRanges
     */
    public function it_can_convert_the_valid_language_range_to_a_valid_language(
        array $range,
        string $expectedTag,
        float $expectedQuality
    ) {
        $service = new LanguageFactory();
        $language = $service->makeFromLanguageRange(...$range);

        $this->assertEquals($expectedTag, $language->getTag());
        $this->assertEquals($expectedQuality, $language->getQuality());
        $this->assertTrue($language->isValid());
    }

    public static function provideDifferentValidLanguageRanges(): array
    {
        return [
            'a language range without a quality results in the language with fallback' => [
                [['en'], 0.5],
                'en',
                0.5,
            ],
            'a language range with an empty quality results in the language with fallback' => [
                [['en', ''], 0.5],
                'en',
                0.5,
            ],
            'a language range with a minimum quality results in the language' => [
                [['en', 0], 1],
                'en',
                0,
            ],
            'a language range with a valid quality results in the language' => [
                [['en', 0.5], 1],
                'en',
                0.5,
            ],
            'a language range with a maximum quality results in the language' => [
                [['en', 1], 1],
                'en',
                1,
            ],
        ];
    }
}
