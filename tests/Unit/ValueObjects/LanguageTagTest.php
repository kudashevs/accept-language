<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit\ValueObjects;

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
            'a one character results in no change (refers to MINIMUM_PRIMARY_SUBTAG_LENGTH constant)' => [
                'a',
                'a',
            ],
            'four characters results in no change (refers to MAXIMUM_PRIMARY_SUBTAG_LENGTH constant)' => [
                'alfa',
                'alfa',
            ],
            'a language tag with a number results in no change' => [
                'a2',
                'a2',
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
            'a two-letter language tag results in the language' => [
                'en',
                'en',
            ],
            'a two-letter language tag with region subtag results in the language' => [
                'de-DE',
                'de-DE',
            ],
            'a two-letter language tag with script subtag results in the language' => [
                'de-Latn',
                'de-Latn',
            ],
            'a two-letter language tag with extlang, script, and region subtags results in the language' => [
                'de-gsg-Latn-DE',
                'de-gsg-Latn-DE',
            ],
            'a three-letter language tag results in the language' => [
                'ast',
                'ast',
            ],
            'a three-letter language tag with region subtag results in the language' => [
                'ast-ES',
                'ast-ES',
            ],
            'a three-letter language tag with script subtag results in the language' => [
                'ast-Latn',
                'ast-Latn',
            ],
            'a three-letter language tag with extlang, script, and region subtags results in the language' => [
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
            'a two-letter language tag results in no change' => [
                [
                    'separator' => '_',
                ],
                'en',
                'en',
            ],
            'a two-letter language tag with script with hyphen separator results in no change' => [
                [
                    'separator' => '-',
                ],
                'sr-Latn',
                'sr-Latn',
            ],
            'a two-letter language tag with script and region with hyphen separator results in no change' => [
                [
                    'separator' => '-',
                ],
                'sr-Latn-RS',
                'sr-Latn-RS',
            ],
            'a two-letter language tag with extlang, script, and region with hyphen separator results in remove extlang' => [
                [
                    'separator' => '-',
                ],
                'zh-yue-Hant-CN',
                'zh-Hant-CN',
            ],
            'a three-letter language tag with script with hyphen separator results in no change' => [
                [
                    'separator' => '-',
                ],
                'sgn-Latn',
                'sgn-Latn',
            ],
            'a three-letter language tag with script and region with hyphen separator results in no change' => [
                [
                    'separator' => '-',
                ],
                'sgn-Latn-RS',
                'sgn-Latn-RS',
            ],
            'a three-letter language tag with extlang, script, and region with hyphen separator results in remove extlang' => [
                [
                    'separator' => '-',
                ],
                'sgn-ysl-Latn-RS',
                'sgn-Latn-RS',
            ],
            'a two-letter language tag with script with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'sr-Latn',
                'sr_Latn',
            ],
            'a two-letter language tag with script and region with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'sr-Latn-RS',
                'sr_Latn_RS',
            ],
            'a two-letter language tag with extlang, script, and region with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'zh-yue-Hant-CN',
                'zh_Hant_CN',
            ],
            'a three-letter language tag with script with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'sgn-Latn',
                'sgn_Latn',
            ],
            'a three-letter language tag with script and region with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'sgn-Latn-RS',
                'sgn_Latn_RS',
            ],
            'a three-letter language tag with extlang, script, and region with underscore separator change the separator' => [
                [
                    'separator' => '_',
                ],
                'sgn-ysl-Latn-RS',
                'sgn_Latn_RS',
            ],
            'a two-letter language tag with script with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'sr-Latn',
                'sr~Latn',
            ],
            'a two-letter language tag with script and region with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'sr-Latn-RS',
                'sr~Latn~RS',
            ],
            'a two-letter language tag with extlang, script, and region with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'zh-yue-Hant-CN',
                'zh~Hant~CN',
            ],
            'a three-letter language tag with script with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'sr-Latn',
                'sr~Latn',
            ],
            'a three-letter language tag with script and region with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'sgn-Latn-RS',
                'sgn~Latn~RS',
            ],
            'a three-letter language tag with extlang, script, and region with tilde separator change the separator' => [
                [
                    'separator' => '~',
                ],
                'sgn-ysl-Latn-RS',
                'sgn~Latn~RS',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentLanguageValues
     */
    public function it_can_retrieve_a_primary_subtag_and_subtags(array $options, string $input, array $expected)
    {
        $language = new LanguageTag($input, $options);

        $this->assertSame($expected, $language->getSubtags());
        $this->assertSame($expected[0], $language->getPrimarySubtag());
        $this->assertTrue($language->isValid());
    }

    public function provideDifferentLanguageValues(): array
    {
        return [
            'a two-letter language tag results in the subtags' => [
                [
                    'separator' => '-',
                ],
                'en',
                ['en'],
            ],
            'a two-letter language tag with script with hyphen separator results in the subtags' => [
                [
                    'separator' => '-',
                ],
                'sr-Latn',
                ['sr', 'Latn'],
            ],
            'a two-letter language tag with script and region with hyphen separator results in the subtags' => [
                [
                    'separator' => '-',
                ],
                'sr-Latn-RS',
                ['sr', 'Latn', 'RS'],
            ],
            'a two-letter language tag with extlang, script, and region with hyphen separator results in the subtags' => [
                [
                    'separator' => '-',
                ],
                'zh-yue-Hant-CN',
                ['zh', 'Hant', 'CN'],
            ],
            'a two-letter language tag with extlang, script, and region with underscore separator results in the subtags' => [
                [
                    'separator' => '_',
                ],
                'zh-yue-Hant-CN',
                ['zh', 'Hant', 'CN'],
            ],
            'a two-letter language tag with extlang, script, and region with tilde separator results in the subtags' => [
                [
                    'separator' => '~',
                ],
                'zh-yue-Hant-CN',
                ['zh', 'Hant', 'CN'],
            ],
        ];
    }
}
