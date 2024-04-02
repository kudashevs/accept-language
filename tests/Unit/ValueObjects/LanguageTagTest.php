<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\ValueObjects;

use Kudashevs\AcceptLanguage\ValueObjects\LanguageTag;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated(): void
    {
        $tag = new LanguageTag('en');

        $this->assertNotEmpty($tag->getTag());
        $this->assertTrue($tag->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentInvalidLanguageValues
     */
    public function it_can_handle_an_ivalid_language_tag(string $input, string $expected): void
    {
        $quality = new LanguageTag($input);

        $this->assertSame($expected, $quality->getTag());
        $this->assertFalse($quality->isValid());
    }

    public static function provideDifferentInvalidLanguageValues(): array
    {
        return [
            'an empty tag results in no change and the invalid language' => [
                '',
                '',
            ],
            'a language tag with a number results in no change and the invalid language' => [
                'a2',
                'a2',
            ],
            'a language tag with space results in no change and the invalid language' => [
                'de Latn',
                'de Latn',
            ],
            'a language tag longer than the maximum length results in no change and the invalid language' => [
                'verywrong',
                'verywrong',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentValidLanguageValues
     */
    public function it_can_create_a_valid_language_tag_from_the_valid_data(string $input, string $expected): void
    {
        $language = new LanguageTag($input, [
            'with_extlang' => true,
            'with_script' => true,
            'with_region' => true,
        ]);

        $this->assertSame($expected, $language->getTag());
        $this->assertTrue($language->isValid());
    }

    public static function provideDifferentValidLanguageValues(): array
    {
        return [
            'a one character results in the valid language (refers to the MINIMUM_PRIMARY_SUBTAG_LENGTH constant)' => [
                'a',
                'a',
            ],
            'a two-letter language tag results in the valid language' => [
                'en',
                'en',
            ],
            'four characters results in the valid language (refers to old value of the maximum length constant)' => [
                'alfa',
                'alfa',
            ],
            'eight characters results in the valid language (refers to the MAXIMUM_PRIMARY_SUBTAG_LENGTH constant)' => [
                'enochian',
                'enochian',
            ],
            'a language tag with one-letter primary subtag results in the valid language' => [
                'a-t',
                'a-t',
            ],
            'a two-letter language tag with region subtag results in the valid language' => [
                'de-DE',
                'de-DE',
            ],
            'a two-letter language tag with script subtag results in the valid language' => [
                'de-Latn',
                'de-Latn',
            ],
            'a two-letter language tag with extlang, script, and region subtags results in the valid language' => [
                'de-gsg-Latn-DE',
                'de-gsg-Latn-DE',
            ],
            'a three-letter language tag results in the valid language' => [
                'ast',
                'ast',
            ],
            'a three-letter language tag with region subtag results in the valid language' => [
                'ast-ES',
                'ast-ES',
            ],
            'a three-letter language tag with script subtag results in the valid language' => [
                'ast-Latn',
                'ast-Latn',
            ],
            'a three-letter language tag with extlang, script, and region subtags results in the valid language' => [
                'ast-ssp-Latn-ES',
                'ast-ssp-Latn-ES',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageValuesWithDifferentSubtagOptions
     */
    public function it_can_normalize_with_the_provided_options(string $input, array $options, string $expected): void
    {
        $language = new LanguageTag($input, $options);

        $this->assertSame($expected, $language->getTag());
        $this->assertTrue($language->isValid());
    }

    public static function provideDifferentLanguageValuesWithDifferentSubtagOptions(): array
    {
        return [
            'a two-letter language tag with all options disabled results in the language' => [
                'de-gsg-Latn-DE',
                [
                    'with_extlang' => false,
                    'with_script' => false,
                    'with_region' => false,
                ],
                'de',
            ],
            'a two-letter language tag with extlang option results in the language' => [
                'de-gsg-Latn-DE',
                [
                    'with_extlang' => true,
                    'with_script' => false,
                    'with_region' => false,
                ],
                'de-gsg',
            ],
            'a two-letter language tag with region option results in the language' => [
                'de-gsg-Latn-DE',
                [
                    'with_extlang' => false,
                    'with_script' => false,
                    'with_region' => true,
                ],
                'de-DE',
            ],
            'a two-letter language tag with script option results in the language' => [
                'de-gsg-Latn-DE',
                [
                    'with_extlang' => false,
                    'with_script' => true,
                    'with_region' => false,
                ],
                'de-Latn',
            ],
            'a two-letter language tag with all options enabled results in the language' => [
                'de-gsg-Latn-DE',
                [
                    'with_extlang' => true,
                    'with_script' => true,
                    'with_region' => true,
                ],
                'de-gsg-Latn-DE',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageValuesWithDifferentSeparatorOption
     */
    public function it_can_normalize_with_a_provided_separator(string $input, array $options, string $expected): void
    {
        $language = new LanguageTag($input, $options);

        $this->assertSame($expected, $language->getTag());
        $this->assertTrue($language->isValid());
    }

    public static function provideDifferentLanguageValuesWithDifferentSeparatorOption(): array
    {
        return [
            'a two-letter language tag results in no change' => [
                'en',
                [
                    'separator' => '_',
                ],
                'en',
            ],
            'a two-letter hyphenated language tag with script with hyphen separator results in no separator change' => [
                'sr-Latn',
                [
                    'separator' => '-',
                ],
                'sr-Latn',
            ],
            'a two-letter hyphenated language tag with script and region with hyphen separator results in no separator change' => [
                'sr-Latn-RS',
                [
                    'separator' => '-',
                ],
                'sr-Latn-RS',
            ],
            'a two-letter hyphenated language tag with extlang, script, and region with hyphen separator remove extlang and no separator change' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '-',
                ],
                'zh-Hant-CN',
            ],
            'a two-letter hyphenated language tag with script with underscore separator change the separator' => [
                'sr-Latn',
                [
                    'separator' => '_',
                ],
                'sr_Latn',
            ],
            'a two-letter hyphenated language tag with script and region with underscore separator change the separator' => [
                'sr-Latn-RS',
                [
                    'separator' => '_',
                ],
                'sr_Latn_RS',
            ],
            'a two-letter hyphenated language tag with extlang, script, and region with underscore separator remove extlang and change the separator' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '_',
                ],
                'zh_Hant_CN',
            ],
            'a two-letter underscored language tag with script with underscore separator results in no separator change' => [
                'sr_Latn',
                [
                    'separator' => '_',
                ],
                'sr_Latn',
            ],
            'a two-letter underscored language tag with script and region with underscore separator results in no separator change' => [
                'sr_Latn_RS',
                [
                    'separator' => '_',
                ],
                'sr_Latn_RS',
            ],
            'a two-letter underscored language tag with extlang, script, and region with underscore separator results in remove extlang and no separator change' => [
                'zh_yue_Hant_CN',
                [
                    'separator' => '_',
                ],
                'zh_Hant_CN',
            ],
            'a two-letter underscored language tag with script with hyphen separator change the separator' => [
                'sr_Latn',
                [
                    'separator' => '-',
                ],
                'sr-Latn',
            ],
            'a two-letter underscored language tag with script and region with hyphen separator change the separator' => [
                'sr_Latn_RS',
                [
                    'separator' => '-',
                ],
                'sr-Latn-RS',
            ],
            'a two-letter underscored language tag with extlang, script, and region with hyphen separator results in remove extlang and change the separator' => [
                'zh_yue_Hant_CN',
                [
                    'separator' => '-',
                ],
                'zh-Hant-CN',
            ],
            'a two-letter hyphenated language tag with script with tilde separator change the separator' => [
                'sr-Latn',
                [
                    'separator' => '~',
                ],
                'sr~Latn',
            ],
            'a two-letter hyphenated language tag with script and region with tilde separator change the separator' => [
                'sr-Latn-RS',
                [
                    'separator' => '~',
                ],
                'sr~Latn~RS',
            ],
            'a two-letter hyphenated language tag with extlang, script, and region with tilde separator results in remove extlang and change the separator' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '~',
                ],
                'zh~Hant~CN',
            ],
            'a two-letter underscored language tag with script with tilde separator change the separator' => [
                'sr_Latn',
                [
                    'separator' => '~',
                ],
                'sr~Latn',
            ],
            'a two-letter underscored language tag with script and region with tilde separator change the separator' => [
                'sr_Latn_RS',
                [
                    'separator' => '~',
                ],
                'sr~Latn~RS',
            ],
            'a two-letter underscored language tag with extlang, script, and region with tilde separator remove extlang and change the separator' => [
                'zh_yue_Hant_CN',
                [
                    'separator' => '~',
                ],
                'zh~Hant~CN',
            ],
            'a three-letter language tag results in no change' => [
                'sgn',
                [
                    'separator' => '_',
                ],
                'sgn',
            ],
            'a three-letter hyphenated language tag with script with hyphen separator results in no separator change' => [
                'sgn-Latn',
                [
                    'separator' => '-',
                ],
                'sgn-Latn',
            ],
            'a three-letter hyphenated language tag with script and region with hyphen separator results in no separator change' => [
                'sgn-Latn-RS',
                [
                    'separator' => '-',
                ],
                'sgn-Latn-RS',
            ],
            'a three-letter hyphenated language tag with extlang, script, and region with hyphen separator results in remove extlang and no separator change' => [
                'sgn-ysl-Latn-RS',
                [
                    'separator' => '-',
                ],
                'sgn-Latn-RS',
            ],
            'a three-letter hyphenated language tag with script with underscore separator change the separator' => [
                'sgn-Latn',
                [
                    'separator' => '_',
                ],
                'sgn_Latn',
            ],
            'a three-letter hyphenated language tag with script and region with underscore separator change the separator' => [
                'sgn-Latn-RS',
                [
                    'separator' => '_',
                ],
                'sgn_Latn_RS',
            ],
            'a three-letter hyphenated language tag with extlang, script, and region with underscore separator change the separator' => [
                'sgn-ysl-Latn-RS',
                [
                    'separator' => '_',
                ],
                'sgn_Latn_RS',
            ],
            'a three-letter underscored language tag with script with underscore separator results in no separator change' => [
                'sgn_Latn',
                [
                    'separator' => '_',
                ],
                'sgn_Latn',
            ],
            'a three-letter underscored language tag with script and region with underscore separator results in no separator change' => [
                'sgn_Latn_RS',
                [
                    'separator' => '_',
                ],
                'sgn_Latn_RS',
            ],
            'a three-letter underscored language tag with extlang, script, and region with underscore results in remove extlang and no separator change' => [
                'sgn_ysl_Latn_RS',
                [
                    'separator' => '_',
                ],
                'sgn_Latn_RS',
            ],
            'a three-letter underscored language tag with script with hyphen separator change the separator' => [
                'sgn_Latn',
                [
                    'separator' => '-',
                ],
                'sgn-Latn',
            ],
            'a three-letter underscored language tag with script and region with hyphen separator change the separator' => [
                'sgn_Latn_RS',
                [
                    'separator' => '-',
                ],
                'sgn-Latn-RS',
            ],
            'a three-letter underscored language tag with extlang, script, and region with hyphen separator change the separator' => [
                'sgn_ysl_Latn_RS',
                [
                    'separator' => '-',
                ],
                'sgn-Latn-RS',
            ],
            'a three-letter hyphenated language tag with script with tilde separator change the separator' => [
                'sgn-Latn',
                [
                    'separator' => '~',
                ],
                'sgn~Latn',
            ],
            'a three-letter hyphenated language tag with script and region with tilde separator change the separator' => [
                'sgn-Latn-RS',
                [
                    'separator' => '~',
                ],
                'sgn~Latn~RS',
            ],
            'a three-letter hyphenated language tag with extlang, script, and region with tilde separator change the separator' => [
                'sgn-ysl-Latn-RS',
                [
                    'separator' => '~',
                ],
                'sgn~Latn~RS',
            ],
            'a three-letter underscored language tag with script with tilde separator change the separator' => [
                'sgn_Latn',
                [
                    'separator' => '~',
                ],
                'sgn~Latn',
            ],
            'a three-letter underscored language tag with script and region with tilde separator change the separator' => [
                'sgn_Latn_RS',
                [
                    'separator' => '~',
                ],
                'sgn~Latn~RS',
            ],
            'a three-letter underscored language tag with extlang, script, and region with tilde separator change the separator' => [
                'sgn_ysl_Latn_RS',
                [
                    'separator' => '~',
                ],
                'sgn~Latn~RS',
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_handle_an_empty_separator(): void
    {
        // The empty separator doesn't have a lot of sense, but let's consider it as a boundary case.
        $language = new LanguageTag('de-DE', [
            'separator' => '',
        ]);

        $this->assertCount(2, $language->getSubtags());
        $this->assertTrue($language->isValid());
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageValues
     */
    public function it_can_retrieve_a_primary_subtag_and_subtags(string $input, array $options, array $expected): void
    {
        $language = new LanguageTag($input, $options);

        $this->assertSame($expected, $language->getSubtags());
        $this->assertSame($expected[0], $language->getPrimarySubtag());
        $this->assertTrue($language->isValid());
    }

    public static function provideDifferentLanguageValues(): array
    {
        return [
            'a two-letter language tag with script and region with empty separator results in the subtags' => [
                'sr-Latn-RS',
                [
                    'separator' => '',
                ],
                ['sr', 'Latn', 'RS'],
            ],
            'a two-letter language tag results in the subtags' => [
                'en',
                [
                    'separator' => '-',
                ],
                ['en'],
            ],
            'a two-letter language tag with script with hyphen separator results in the subtags' => [
                'sr-Latn',
                [
                    'separator' => '-',
                ],
                ['sr', 'Latn'],
            ],
            'a two-letter language tag with script and region with hyphen separator results in the subtags' => [
                'sr-Latn-RS',
                [
                    'separator' => '-',
                ],
                ['sr', 'Latn', 'RS'],
            ],
            'a two-letter language tag with extlang, script, and region with hyphen separator results in the subtags' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '-',
                ],
                ['zh', 'Hant', 'CN'],
            ],
            'a two-letter language tag with extlang, script, and region with underscore separator results in the subtags' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '_',
                ],
                ['zh', 'Hant', 'CN'],
            ],
            'a two-letter language tag with extlang, script, and region with tilde separator results in the subtags' => [
                'zh-yue-Hant-CN',
                [
                    'separator' => '~',
                ],
                ['zh', 'Hant', 'CN'],
            ],
        ];
    }
}
