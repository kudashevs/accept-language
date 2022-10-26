<?php

namespace Kudashevs\AcceptLanguage\Tests\LanguageTags;

use Kudashevs\AcceptLanguage\LanguageTags\LanguageTag;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $instance = new LanguageTag('en', 1);

        $this->assertNotEmpty($instance->getTag());
        $this->assertNotEmpty($instance->getQuality());
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageRanges
     */
    public function it_can_create_a_language_tag(array $input, string $tag, float $quality)
    {
        $instance = new LanguageTag(...$input);

        $this->assertSame($tag, $instance->getTag());
        $this->assertSame($quality, $instance->getQuality());
    }

    public function provideDifferentLanguageRanges(): array
    {
        return [
            'an empty tag results in an empty language tag' => [
                ['', 1],
                '',
                0.0,
            ],
            'a tag with space results in empty language tag' => [
                [' ', 1],
                '',
                0.0,
            ],
            'a two-letter primary language tag results in a language tag' => [
                ['en', 1],
                'en',
                1.0,
            ],
        ];
    }
}
