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
     * @dataProvider provideDifferentLanguageRanges
     */
    public function it_can_create_a_language(array $input, string $tag, $quality)
    {
        $instance = new Language(...$input);

        $this->assertSame($tag, $instance->getTag());
        $this->assertSame($quality, $instance->getQuality());
    }

    public function provideDifferentLanguageRanges(): array
    {
        return [
            'an empty tag results in an empty language tag' => [
                ['', 0],
                '',
                0,
            ],
            'a tag with space results in empty language tag' => [
                [' ', 0.5],
                ' ',
                0.5,
            ],
            'a two-letter primary language tag results in a language tag' => [
                ['en', 1],
                'en',
                1,
            ],
        ];
    }
}
