<?php

namespace Kudashevs\AcceptLanguage\Tests\ValueObjects;

use Kudashevs\AcceptLanguage\ValueObjects\LanguageTag;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $tag = new LanguageTag('en');

        $this->assertNotEmpty($tag->getTag());
        $this->assertTrue($tag->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentInvalidLanguageValues
     */
    public function it_can_handle_an_ivalid_language_tag(string $input, string $expected)
    {
        $quality = new LanguageTag($input);

        $this->assertSame($expected, $quality->getTag());
        $this->assertFalse($quality->isValid());
    }

    public function provideDifferentInvalidLanguageValues(): array
    {
        return [
            'an empty tag results in no change' => [
                '',
                '',
            ],
            'a letter results in no change' => [
                'a',
                'a',
            ],
            'a language tag with space results in no change' => [
                'de Latn',
                'de Latn',
            ],
            'a language tag with one-letter primary subtag results in no change' => [
                'a-t',
                'a-t',
            ],
            'a language tag with five-letter primary subtag results in no change' => [
                'wrong',
                'wrong',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentValidLanguageValues
     */
    public function it_can_create_a_valid_language_tag_from_the_valid_data(string $input, string $expected)
    {
        $language = new LanguageTag($input, [
            'with_extlang' => true,
            'with_script' => true,
            'with_region' => true,
        ]);

        $this->assertSame($expected, $language->getTag());
        $this->assertTrue($language->isValid());
    }

    public function provideDifferentValidLanguageValues(): array
    {
        return [
            'a two-letter primary results in the language' => [
                'en',
                'en',
            ],
            'a two-letter primary with region subtag results in the language' => [
                'de-DE',
                'de-DE',
            ],
            'a two-letter primary with script subtag results in the language' => [
                'de-Latn',
                'de-Latn',
            ],
            'a two-letter primary with extlang, script, and region subtags results in the language' => [
                'de-gsg-Latn-DE',
                'de-gsg-Latn-DE',
            ],
            'a three-letter primary result in the language' => [
                'ast',
                'ast',
            ],
            'a three-letter primary with region subtag results in the language' => [
                'ast-ES',
                'ast-ES',
            ],
            'a three-letter primary with script subtag results in the language' => [
                'ast-Latn',
                'ast-Latn',
            ],
            'a three-letter primary with extlang, script, and region subtags results in the language' => [
                'ast-ssp-Latn-ES',
                'ast-ssp-Latn-ES',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageValuesWithDifferentSubtagOptions
     */
    public function it_can_normalize_with_the_provided_options(array $options, string $input, string $expected)
    {
        $language = new LanguageTag($input, $options);

        $this->assertSame($expected, $language->getTag());
        $this->assertTrue($language->isValid());
    }

    public function provideDifferentLanguageValuesWithDifferentSubtagOptions(): array
    {
        return [
            'a two-letter language tag with all options disabled results in the language' => [
                [
                    'with_extlang' => false,
                    'with_script' => false,
                    'with_region' => false,
                ],
                'de-gsg-Latn-DE',
                'de',
            ],
            'a two-letter language tag with extlang option results in the language' => [
                [
                    'with_extlang' => true,
                    'with_script' => false,
                    'with_region' => false,
                ],
                'de-gsg-Latn-DE',
                'de-gsg',
            ],
            'a two-letter language tag with region option results in the language' => [
                [
                    'with_extlang' => false,
                    'with_script' => false,
                    'with_region' => true,
                ],
                'de-gsg-Latn-DE',
                'de-DE',
            ],
            'a two-letter language tag with script option results in the language' => [
                [
                    'with_extlang' => false,
                    'with_script' => true,
                    'with_region' => false,
                ],
                'de-gsg-Latn-DE',
                'de-Latn',
            ],
            'a two-letter language tag with all options enabled results in the language' => [
                [
                    'with_extlang' => true,
                    'with_script' => true,
                    'with_region' => true,
                ],
                'de-gsg-Latn-DE',
                'de-gsg-Latn-DE',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageValuesWithDifferentSeparatorOption
     */
    public function it_can_normalize_with_a_provided_separator(array $options, string $input, string $expected)
    {
        $language = new LanguageTag($input, $options);

        $this->assertSame($expected, $language->getTag());
        $this->assertTrue($language->isValid());
    }

    public function provideDifferentLanguageValuesWithDifferentSeparatorOption(): array
    {
        return [
            'a two-letter primary result in no change' => [
                [
                    'separator' => '_',
                ],
                'en',
                'en',
            ],
            'a two-letter primary with script with hyphen separator result in no change' => [
                [
                    'separator' => '-',
                ],
                'sr-Latn',
                'sr-Latn',
            ],
            'a two-letter primary with script and region with hyphen separator result in no change' => [
                [
                    'separator' => '-',
                ],
                'sr-Latn-RS',
                'sr-Latn-RS',
            ],
            'a two-letter primary with extlang, script, and region with hyphen separator result in no change' => [
                [
                    'separator' => '-',
                ],
                'zh-yue-Hant-CN',
                'zh-Hant-CN',
            ],
            'a three-letter primary with script with hyphen separator result in no change' => [
                [
                    'separator' => '-',
                ],
                'sgn-Latn',
                'sgn-Latn',
            ],
            'a two-letter primary with script with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'sr-Latn',
                'sr_Latn',
            ],
            'a two-letter primary with script and region with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'sr-Latn-RS',
                'sr_Latn_RS',
            ],
            'a two-letter primary with extlang, script, and region with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'zh-yue-Hant-CN',
                'zh_Hant_CN',
            ],
            'a three-letter primary with script with underscore separator no change' => [
                [
                    'separator' => '_',
                ],
                'sgn-Latn',
                'sgn_Latn',
            ],
            'a two-letter primary with script with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'sr-Latn',
                'sr~Latn',
            ],
            'a two-letter primary with script and region with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'sr-Latn-RS',
                'sr~Latn~RS',
            ],
            'a two-letter primary with extlang, script, and region with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'zh-yue-Hant-CN',
                'zh~Hant~CN',
            ],
            'a three-letter primary with script with tilde separator no change' => [
                [
                    'separator' => '~',
                ],
                'sr-Latn',
                'sr~Latn',
            ],
        ];
    }
}
