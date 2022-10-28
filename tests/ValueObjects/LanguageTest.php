<?php

namespace Kudashevs\AcceptLanguage\Tests\ValueObjects;

use Kudashevs\AcceptLanguage\ValueObjects\Language;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $instance = new Language('en', 1);

        $this->assertNotEmpty($instance->getTag());
        $this->assertNotEmpty($instance->getQuality());
        $this->assertTrue($instance->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentValidLanguageRanges
     */
    public function it_can_create_a_valid_language(array $input, string $tag, $quality)
    {
        $instance = new Language(...$input);

        $this->assertSame($tag, $instance->getTag());
        $this->assertSame($quality, $instance->getQuality());
        $this->assertTrue($instance->isValid());
    }

    public function provideDifferentValidLanguageRanges(): array
    {
        return [
            'a language tag with a minimum quality results in a valid language' => [
                ['en', 0],
                'en',
                0,
            ],
            'a language tag with a valid quality results in a valid language' => [
                ['en', 0.5],
                'en',
                0.5,
            ],
            'a language tag with a maximum quality results in a valid language' => [
                ['en', 1],
                'en',
                1,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentInvalidLanguageRanges
     */
    public function it_can_create_a_non_valid_language(array $input, string $tag, $quality)
    {
        $instance = new Language(...$input);

        $this->assertSame($tag, $instance->getTag());
        $this->assertSame($quality, $instance->getQuality());
        $this->assertFalse($instance->isValid());
    }

    public function provideDifferentInvalidLanguageRanges(): array
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
                ['valid', -1],
                'valid',
                -1,
            ],
            'a valid language tag with a quality lesser than minimum results in a non valid language' => [
                ['valid', -0.001],
                'valid',
                -0.001,
            ],
            'a valid language tag with a quality greater than maximum results in a non valid language' => [
                ['valid', 1.001],
                'valid',
                1.001,
            ],
        ];
    }
}
