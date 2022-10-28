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
            'a two-letter primary language tag results in a valid language tag' => [
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
            'an empty tag results in a non valid language' => [
                ['', 0],
                '',
                0,
            ],
            'a tag with space results in a non valid language' => [
                [' ', 0.5],
                ' ',
                0.5,
            ],
        ];
    }
}
