<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionType;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionValue;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AcceptLanguageTest extends TestCase
{
    protected const DEFAULT_LANGUAGE = 'en';

    protected const DEFAULT_QUALITY = 1;

    /**
     * @test
     * @dataProvider provideDifferentWrongOptions
     */
    public function it_can_throw_an_exception_when_an_option_of_the_wrong_type(array $options): void
    {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('The option "' . key($options) . '" has a wrong value type');

        new AcceptLanguage($options);
    }

    public static function provideDifferentWrongOptions(): array
    {
        return [
            'an http_accept_language option with a wrong value' => [
                ['http_accept_language' => null],
            ],
            'a default_language option with a wrong value' => [
                ['default_language' => null],
            ],
            'an accepted_languages option with a wrong value' => [
                ['accepted_languages' => null],
            ],
            'a two_letter_only option with a wrong value' => [
                ['two_letter_only' => null],
            ],
            'a separator option with a wrong value' => [
                ['separator' => null],
            ],
        ];
    }

    /** @test */
    public function it_can_throw_an_exception_when_a_wrong_default_language(): void
    {
        $this->expectException(InvalidOptionValue::class);
        $this->expectExceptionMessage('The value "verywrong_language" is invalid');

        new AcceptLanguage([
            'default_language' => 'verywrong_language',
        ]);
    }

    /** @test */
    public function it_can_handle_an_unknown_option_without_throwing_an_exception(): void
    {
        $options = [
            'unknown_option' => false,
        ];

        $service = new AcceptLanguage($options);
        $service->process();

        // assert that no exceptions were thrown
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_retain_the_original_header(): void
    {
        $options = [
            'http_accept_language' => 'en-US,en;q=0.5',
        ];

        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('en-US,en;q=0.5', $service->getHeader());
    }

    /**
     * @test
     */
    public function it_can_retrieve_a_default_language_when_no_header_and_no_options_are_provided(): void
    {
        $service = new AcceptLanguage();
        $service->process();

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getLanguage());
        $this->assertSame(self::DEFAULT_QUALITY, $service->getPreferredLanguageQuality());
        $this->assertSame(self::DEFAULT_QUALITY, $service->getQuality());
    }

    /**
     * @test
     * @dataProvider provideDifferentDefaultLanguageOptions
     */
    public function it_can_retrieve_a_default_language_when_the_default_language_is_set(
        array $options,
        string $expected
    ): void {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expected, $service->getPreferredLanguage());
        $this->assertSame($expected, $service->getLanguage());
    }

    public static function provideDifferentDefaultLanguageOptions(): array
    {
        return [
            'a two-letter language tag as the default language' => [
                [
                    'default_language' => 'de',
                ],
                'de',
            ],
            'a two-letter hyphenated language tag with region subtag change the separator' => [
                [
                    'default_language' => 'de-DE',
                ],
                'de_DE',
            ],
            'a two-letter hyphenated language tag with script subtag remove script subtag' => [
                [
                    'default_language' => 'de-Latn',
                ],
                'de',
            ],
            'a two-letter hyphenated language tag with script and region subtags remove script subtag and change the separator' => [
                [
                    'default_language' => 'de-Latn-DE',
                ],
                'de_DE',
            ],
            'a two-letter hyphenated language tag with extlang, script, and region subtags remove subtags and change the separator' => [
                [
                    'default_language' => 'de-gsg-Latn-DE',
                ],
                'de_DE',
            ],
            'a two-letter underscored language tag with region subtag results in no change' => [
                [
                    'default_language' => 'de_DE',
                ],
                'de_DE',
            ],
            'a two-letter underscored language tag with script subtag remove script subtag' => [
                [
                    'default_language' => 'de_Latn',
                ],
                'de',
            ],
            'a two-letter underscored language tag with script and region subtags remove script subtag and no separator change' => [
                [
                    'default_language' => 'de_Latn_DE',
                ],
                'de_DE',
            ],
            'a two-letter underscored language tag with extlang, script, and region subtags remove subtags and no separator change' => [
                [
                    'default_language' => 'de_gsg_Latn_DE',
                ],
                'de_DE',
            ],
            'a three-letter language tag as the default language' => [
                [
                    'default_language' => 'sgn',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a three-letter hyphenated language tag with region subtag change the separator' => [
                [
                    'default_language' => 'sgn-RS',
                    'two_letter_only' => false,
                ],
                'sgn_RS',
            ],
            'a three-letter hyphenated language tag with script subtag remove script subtag' => [
                [
                    'default_language' => 'sgn-Latn',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a three-letter hyphenated language tag with region and script subtags remove script subtag and change the separator' => [
                [
                    'default_language' => 'sgn-Latn-RS',
                    'two_letter_only' => false,
                ],
                'sgn_RS',
            ],
            'a three-letter hyphenated language tag with extlang, script, and region subtags remove subtags and change the separator' => [
                [
                    'default_language' => 'sgn-ysl-Latn-RS',
                    'two_letter_only' => false,
                ],
                'sgn_RS',
            ],
            'a three-letter underscored language tag with region subtag results in no change' => [
                [
                    'default_language' => 'sgn_RS',
                    'two_letter_only' => false,
                ],
                'sgn_RS',
            ],
            'a three-letter underscored language tag with script subtag remove script subtag' => [
                [
                    'default_language' => 'sgn_Latn',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a three-letter underscored language tag with region and script subtags remove script subtag and no separator change' => [
                [
                    'default_language' => 'sgn_Latn_RS',
                    'two_letter_only' => false,
                ],
                'sgn_RS',
            ],
            'a three-letter underscored language tag with extlang, script, and region subtags remove subtags and no separator change' => [
                [
                    'default_language' => 'sgn_ysl_Latn_RS',
                    'two_letter_only' => false,
                ],
                'sgn_RS',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentDefaultLanguageOptionValuesWithFormatting
     */
    public function it_can_format_the_default_language_option_according_to_the_settings(
        array $options,
        string $expected
    ): void {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expected, $service->getPreferredLanguage());
        $this->assertSame($expected, $service->getLanguage());
    }

    public static function provideDifferentDefaultLanguageOptionValuesWithFormatting(): array
    {
        return [
            'a two-letter language tag with script and region results in the language' => [
                [
                    'default_language' => 'sr-Latn-RS',
                    'accepted_languages' => ['en', 'de', 'es'],
                    'separator' => '~',
                ],
                'sr~RS',
            ],
            'a two-letter language tag with script and region with the use script subtag option set to true results in the language' => [
                [
                    'default_language' => 'sr-Latn-RS',
                    'accepted_languages' => ['en', 'de', 'es'],
                    'use_script_subtag' => true,
                    'separator' => '~',
                ],
                'sr~Latn~RS',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentcAcceptedLanguagesOptionValues
     */
    public function it_can_handle_the_accepted_languages_option(array $options, string $expected): void
    {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expected, $service->getPreferredLanguage());
        $this->assertSame($expected, $service->getLanguage());
    }

    public static function provideDifferentcAcceptedLanguagesOptionValues(): array
    {
        return [
            'it retrieves a first valid language when the accepted languages option is empty' => [
                [
                    'http_accept_language' => 'en-US,en;q=0.5',
                    'accepted_languages' => [],
                ],
                'en_US',
            ],
            'it retrieves a default language when a language is not listed in the accepted languages option' => [
                [
                    'http_accept_language' => 'pp',
                    'accepted_languages' => ['en', 'de', 'fr'],
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'it retrieves a preferred language when a language is listed in the accepted languages option' => [
                [
                    'default_language' => 'es',
                    'accepted_languages' => ['en', 'de', 'es'],
                ],
                'es',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentAcceptedLanguagesOptions
     */
    public function it_can_format_the_accepted_languages_option_according_to_the_settings(
        array $options,
        string $expected
    ): void {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expected, $service->getPreferredLanguage());
        $this->assertSame($expected, $service->getLanguage());
    }

    public static function provideDifferentAcceptedLanguagesOptions(): array
    {
        return [
            'a two-letter language tag with script and region results in the language' => [
                [
                    'default_language' => 'sr-Latn-RS',
                    'accepted_languages' => ['sr-Latn-RS'],
                    'separator' => '_',
                ],
                'sr_RS',
            ],
            'a two-letter language tag with script and region with the use script subtag option set to true results in the language' => [
                [
                    'default_language' => 'sr-Latn-RS',
                    'accepted_languages' => ['sr-Latn-RS'],
                    'use_script_subtag' => true,
                    'separator' => '_',
                ],
                'sr_Latn_RS',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentRequestHeaderValues
     */
    public function it_can_retrieve_a_preferred_language(
        array $options,
        string $expectedLanguage,
        int|float $expectedQuality
    ): void {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expectedLanguage, $service->getPreferredLanguage());
        $this->assertSame($expectedQuality, $service->getPreferredLanguageQuality());
    }

    public static function provideDifferentRequestHeaderValues(): array
    {
        return [
            'any language tag results in default' => [
                ['http_accept_language' => '*'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'any language tag with highest quality results in default' => [
                ['http_accept_language' => '*,de;q=0.7'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'any language tag and language tag with equal quality results in default' => [
                ['http_accept_language' => '*,es,de;q=0.7'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'any language tag and language tag with equal quality results in the language' => [
                ['http_accept_language' => 'es,*,de;q=0.7'],
                'es',
                1,
            ],
            'a two-letter language tag results in the language' => [
                ['http_accept_language' => 'fr'],
                'fr',
                1,
            ],
            'a three-letter language tag results in default' => [
                ['http_accept_language' => 'sgn'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'a three-letter language tag with option results in default' => [
                [
                    'http_accept_language' => 'sgn',
                    'two_letter_only' => false,
                ],
                'sgn',
                1,
            ],
            'a four letters language tag results in default' => [
                ['http_accept_language' => 'test'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'a two-letter language tag with region results in the language' => [
                ['http_accept_language' => 'en-us'],
                'en_US',
                1,
            ],
            'a two-letter language tag with script and region results in the language' => [
                ['http_accept_language' => 'zh-Hant-HK'],
                'zh_HK',
                1,
            ],
            'a two-letter language tag with 0 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=0'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'a three-letter language tag with 0 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0',
                    'two_letter_only' => false,
                ],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'a two-letter language tag with 0.001 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=0.001'],
                'de',
                0.001,
            ],
            'a three-letter language tag with 0.001 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0.001',
                    'two_letter_only' => false,
                ],
                'sgn',
                0.001,
            ],
            'a two-letter language tag with quality language tag results in the language' => [
                ['http_accept_language' => 'de;q=0.5'],
                'de',
                0.5,
            ],
            'a three-letter language tag with quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0.5',
                    'two_letter_only' => false,
                ],
                'sgn',
                0.5,
            ],
            'a two-letter language tag with 0.999 quality language tag results in the language' => [
                ['http_accept_language' => 'de;q=0.999'],
                'de',
                0.999,
            ],
            'a three-letter language tag with 0.999 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0.999',
                    'two_letter_only' => false,
                ],
                'sgn',
                0.999,
            ],
            'a two-letter language tag with 1 quality language tag results in the language' => [
                ['http_accept_language' => 'de;q=1'],
                'de',
                1,
            ],
            'a three-letter language tag with 1 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=1',
                    'two_letter_only' => false,
                ],
                'sgn',
                1,
            ],
            'a two-letter language tag with 1.001 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=1.001'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'a three-letter language tag with 1.001 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=1.001',
                    'two_letter_only' => false,
                ],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'a four letters language tag with quality language tag results in default' => [
                ['http_accept_language' => 'test;q=0.5'],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'a sequence of language tags results in the language' => [
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
                'de',
                1,
            ],
            'an example all lowercase results in the language' => [
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
                'de',
                1,
            ],
            'an example part uppercase results in the language' => [
                ['http_accept_language' => 'de-DE,de;q=0.9,en;q=0.8'],
                'de_DE',
                1,
            ],
            'the mozilla Accept-Language page a basic example results in the language' => [
                ['http_accept_language' => 'de'],
                'de',
                1,
            ],
            'the mozilla Accept-Language page a hyphenated example results in the language' => [
                ['http_accept_language' => 'de-CH'],
                'de_CH',
                1,
            ],
            'the mozilla Accept-Language page a complex example results in the language' => [
                ['http_accept_language' => 'en-US,en;q=0.5'],
                'en_US',
                1,
            ],
            'the mozilla Accept-Language page a complex example with space results in the language' => [
                ['http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'],
                'fr_CH',
                1,
            ],
            'the mozilla Accept-Language page a complex example with space and not present accept language results in default' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['gr'],
                ],
                self::DEFAULT_LANGUAGE,
                self::DEFAULT_QUALITY,
            ],
            'the mozilla Accept-Language page a complex example with space results and present accept language results in the language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['en'],
                ],
                'en',
                0.8,
            ],
            'the mozilla Accept-Language page a complex example with space and default and not present accept language results in provided default' => [
                [
                    'default_language' => 'es',
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['gr'],
                ],
                'es',
                1,
            ],
            'the RFC 2616 14.4 Accept-Language example results in the language' => [
                ['http_accept_language' => 'da, en-gb;q=0.8, en;q=0.7'],
                'da',
                1,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentAcceptedLanguagesValues
     */
    public function it_can_retrieve_a_preferred_language_when_the_accepted_languages_is_set(
        array $options,
        string $expectedLanguage,
        int|float $expectedQuality,
    ): void {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expectedLanguage, $service->getPreferredLanguage());
        $this->assertSame($expectedQuality, $service->getPreferredLanguageQuality());
    }

    public static function provideDifferentAcceptedLanguagesValues(): array
    {
        return [
            'a language that intersects with accepted_languages results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['de'],
                ],
                'de',
                0.7,
            ],
            'a language that intersects with accepted_languages results in the accepted language when it is of quality 1' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['de', 'fr'],
                ],
                'fr',
                1,
            ],
            'a language that intersects with accepted_languages results in the accepted language when it is of quality below 1' => [
                [
                    'http_accept_language' => 'de;q=0.7,fr;q=0.333,es;q=0.333',
                    'accepted_languages' => ['en', 'es'],
                ],
                'es',
                0.333,
            ],
            'a language that intersects with accepted_languages with hyphen separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr-CH'],
                ],
                'fr_CH',
                1,
            ],
            'a language that intersects with accepted_languages with underscore separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr_CH'],
                    'separator' => '_',
                ],
                'fr_CH',
                1,
            ],
            'a language that intersects with accepted_languages with tilde separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr~CH'],
                    'separator' => '~',
                ],
                'fr~CH',
                1,
            ],
            'a language that intersects with accepted_languages formatted with hyphen separator and mixed letters results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-Latn-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr-lAtn-Ch'],
                    'use_script_subtag' => true,
                ],
                'fr_Latn_CH',
                1,
            ],
            'a language that intersects with accepted_languages formatted with underscore separator and mixed letters results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-Latn-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr_lAtn_Ch'],
                    'use_script_subtag' => true,
                    'separator' => '_',
                ],
                'fr_Latn_CH',
                1,
            ],
            'a language that intersects with accepted_languages formatted with tilde separator and mixed letters results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-Latn-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr~lAtn~Ch'],
                    'use_script_subtag' => true,
                    'separator' => '~',
                ],
                'fr~Latn~CH',
                1,
            ],
            'a language that intersects with accepted_languages and a separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr-CH'],
                    'use_script_subtag' => true,
                    'separator' => '_',
                ],
                'fr_CH',
                1,
            ],
            'RFC 2616 14.4 Accept-Language example returns the accepted language when it is of quality 1' => [
                [
                    'http_accept_language' => 'da, en-gb;q=0.8, en;q=0.7',
                    'accepted_languages' => ['da'],
                ],
                'da',
                1,
            ],
            'RFC 2616 14.4 Accept-Language example returns the accepted language when it is of quality below 1' => [
                [
                    'http_accept_language' => 'da, en-gb;q=0.8, en;q=0.7',
                    'accepted_languages' => ['en'],
                ],
                'en',
                0.8,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentTwoLetterOnlyOptions
     * @dataProvider provideDifferentUseExtlangScriptRegionSubtagOptions
     * @dataProvider provideDifferentMatchingOptions
     * @dataProvider provideDifferentSeparatorOptions
     */
    public function it_can_retrieve_a_preferred_language_with_different_options(array $options, string $expected): void
    {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public static function provideDifferentTwoLetterOnlyOptions(): array
    {
        return [
            'returns expected second in row language with the two-letter only option set to true' => [
                [
                    'http_accept_language' => 'ast,en;q=0.8,de;q=0.7,*;q=0.5',
                    'two_letter_only' => true,
                ],
                'en',
            ],
            'returns expected first in row language with the two-letter only option set to false' => [
                [
                    'http_accept_language' => 'ast,en;q=0.8,de;q=0.7,*;q=0.5',
                    'two_letter_only' => false,
                ],
                'ast',
            ],
            'returns expected second of accepted languages with the two-letter only option set to true' => [
                [
                    'http_accept_language' => 'ast,en;q=0.8,de;q=0.7,*;q=0.5',
                    'accepted_languages' => ['ast', 'de'],
                    'two_letter_only' => true,
                ],
                'de',
            ],
            'returns expected first of accepted languages with the two-letter only option set to false' => [
                [
                    'http_accept_language' => 'ast,en;q=0.8,de;q=0.7,*;q=0.5',
                    'accepted_languages' => ['ast', 'de'],
                    'two_letter_only' => false,
                ],
                'ast',
            ],
        ];
    }

    public static function provideDifferentUseExtlangScriptRegionSubtagOptions(): array
    {
        return [
            'returns expected with the use extlang subtag option set to true' => [
                [
                    'http_accept_language' => 'zh-yue-Hans,fr;q=0.8, en;q=0.7',
                    'use_extlang_subtag' => true,
                ],
                'zh_yue',
            ],
            'returns expected with the use extlang subtag option set to false' => [
                [
                    'http_accept_language' => 'zh-yue-Hans,fr;q=0.8, en;q=0.7',
                    'use_extlang_subtag' => false,
                ],
                'zh',
            ],
            'returns expected with the use script subtag option set to true' => [
                [
                    'http_accept_language' => 'zh-Hans,fr;q=0.8, en;q=0.7',
                    'use_script_subtag' => true,
                ],
                'zh_Hans',
            ],
            'returns expected with the use script subtag option set to false' => [
                [
                    'http_accept_language' => 'zh-Hans,fr;q=0.8, en;q=0.7',
                    'use_script_subtag' => false,
                ],
                'zh',
            ],
            'returns expected with the use region subtag option set to true' => [
                [
                    'http_accept_language' => 'zh-Hant-HK,fr;q=0.8, en;q=0.7',
                    'use_script_subtag' => false,
                    'use_region_subtag' => true,
                ],
                'zh_HK',
            ],
            'returns expected with the use region subtag option set to false' => [
                [
                    'http_accept_language' => 'zh-Hant-HK,fr;q=0.8, en;q=0.7',
                    'use_script_subtag' => false,
                    'use_region_subtag' => false,
                ],
                'zh',
            ],
        ];
    }

    public static function provideDifferentMatchingOptions(): array
    {
        return [
            'returns expected default with the exact match only option set to true' => [
                [
                    'http_accept_language' => 'fr-CH',
                    'accepted_languages' => ['fr'],
                    'exact_match_only' => true,
                ],
                'en',
            ],
            'returns expected language with the exact match only option set to false' => [
                [
                    'http_accept_language' => 'fr-CH',
                    'accepted_languages' => ['fr'],
                    'exact_match_only' => false,
                ],
                'fr',
            ],
            'returns expected when language is with region and accepted language is with region and the exact match only option set to true' => [
                [
                    'http_accept_language' => 'fr-CH',
                    'accepted_languages' => ['fr-CH'],
                    'exact_match_only' => true,
                ],
                'fr_CH',
            ],
            'returns expected when language is with region and accepted language is with region and the exact match only option set to false' => [
                [
                    'http_accept_language' => 'fr-CH',
                    'accepted_languages' => ['fr-CH'],
                    'exact_match_only' => false,
                ],
                'fr_CH',
            ],
            'returns expected when language is with script and region and accepted language is with region, and the exact match only option set to true' => [
                [
                    'http_accept_language' => 'fr-Latn-CH',
                    'accepted_languages' => ['fr-CH'],
                    'use_script_subtag' => true,
                    'exact_match_only' => true,
                ],
                'en',
            ],
            'returns expected when language is with script and region and accepted language is with region, and the exact match only option set to false' => [
                [
                    'http_accept_language' => 'fr-Latn-CH',
                    'accepted_languages' => ['fr-CH'],
                    'use_script_subtag' => true,
                    'exact_match_only' => false,
                ],
                'fr_CH',
            ],
            'returns expected when language is with extlang, script and region and accepted language is with region, and the exact match only option set to true' => [
                [
                    'http_accept_language' => 'fr-fsl-Latn-CH',
                    'accepted_languages' => ['fr-CH'],
                    'use_script_subtag' => true,
                    'exact_match_only' => true,
                ],
                'en',
            ],
            'returns expected when language is with extlang, script and region and accepted language is with region, and the exact match only option set to false' => [
                [
                    'http_accept_language' => 'fr-fsl-Latn-CH',
                    'accepted_languages' => ['fr-CH'],
                    'use_script_subtag' => true,
                    'exact_match_only' => false,
                ],
                'fr_CH',
            ],
            'returns expected when language is with extlang, script and region and accepted language is with script and region, and the exact match only option set to true' => [
                [
                    'http_accept_language' => 'fr-fsl-Latn-CH-1694acad', // ?? should be considered as exact match
                    'accepted_languages' => ['fr-Latn-CH'],
                    'use_extlang_subtag' => true,
                    'exact_match_only' => true,
                ],
                'en',
            ],
            'returns expected when language is with extlang, script and region and accepted language is with script and region, and the exact match only option set to false' => [
                [
                    'http_accept_language' => 'fr-fsl-Latn-CH-1694acad',
                    'accepted_languages' => ['fr-Latn-CH'],
                    'with_extlang' => true,
                    'use_script_subtag' => true,
                    'exact_match_only' => false,
                ],
                'fr_Latn_CH',
            ],
            'returns expected default with script and the exact match only option set to true' => [
                [
                    'http_accept_language' => 'de-Latn-AT',
                    'accepted_languages' => ['de-AT'],
                    'use_script_subtag' => true,
                    'exact_match_only' => true,
                ],
                'en',
            ],
            'returns expected language with script and the exact match only option set to false' => [
                [
                    'http_accept_language' => 'de-gsg-AT',
                    'accepted_languages' => ['de-AT'],
                    'use_script_subtag' => true,
                    'exact_match_only' => false,
                ],
                'de_AT',
            ],
            'returns expected default with extlang, script, and region and the exact match only option set to true' => [
                [
                    'http_accept_language' => 'sgn-ase-Latn-US',
                    'accepted_languages' => ['sgn_us'],
                    'two_letter_only' => false,
                    'use_script_subtag' => true,
                    'exact_match_only' => true,
                ],
                'en',
            ],
            'returns expected language with extlang, script, and region and the exact match only option set to false' => [
                [
                    'http_accept_language' => 'sgn-ase-Latn-US',
                    'accepted_languages' => ['sgn_us'],
                    'two_letter_only' => false,
                    'use_script_subtag' => true,
                    'exact_match_only' => false,
                ],
                'sgn_US',
            ],
            'returns default language with a malformed accepted language value' => [
                [
                    'http_accept_language' => 'de-gsg-AT',
                    'accepted_languages' => ['de-AT-gsg'],
                    'use_script_subtag' => true,
                    'exact_match_only' => false,
                ],
                self::DEFAULT_LANGUAGE,
            ],
        ];
    }

    public static function provideDifferentSeparatorOptions(): array
    {
        return [
            'default language is hyphenated and separator is a hyphen' => [
                [
                    'default_language' => 'de-DE',
                    'separator' => '-',
                ],
                'de-DE',
            ],
            'default language is hyphenated and separator is an underscore' => [
                [
                    'default_language' => 'de-DE',
                    'separator' => '_',
                ],
                'de_DE',
            ],
            'default language is underscored and separator is an underscore' => [
                [
                    'default_language' => 'de_DE',
                    'separator' => '_',
                ],
                'de_DE',
            ],
            'default language is underscored and separator is a hyphen' => [
                [
                    'default_language' => 'de_DE',
                    'separator' => '-',
                ],
                'de-DE',
            ],
            'returns expected with the underscore separator' => [
                [
                    'http_accept_language' => 'en-gb,fr;q=0.8, en;q=0.7',
                    'separator' => '_',
                ],
                'en_GB',
            ],
            'returns expected with the hyphen separator' => [
                [
                    'http_accept_language' => 'en-gb,fr;q=0.8, en;q=0.7',
                    'separator' => '-',
                ],
                'en-GB',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentRequestHeadersWithDifferentLanguageLetterLength
     */
    public function it_can_retrieve_a_preferred_language_of_different_length(array $options, string $expected): void
    {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public static function provideDifferentRequestHeadersWithDifferentLanguageLetterLength(): array
    {
        return [
            'a two-letter language tag results in the primary subtag' => [
                [
                    'http_accept_language' => 'de',
                    'two_letter_only' => true,
                ],
                'de',
            ],
            'a two-letter language tag with extlang results in the primary subtag' => [
                [
                    'http_accept_language' => 'de-ger',
                    'two_letter_only' => true,
                ],
                'de',
            ],
            'a two-letter language tag with script results in the primary subtag with script' => [
                [
                    'http_accept_language' => 'de-Latn',
                    'use_script_subtag' => true,
                    'two_letter_only' => true,
                ],
                'de_Latn',
            ],
            'a two-letter language tag with region results in the primary subtag with region' => [
                [
                    'http_accept_language' => 'de-DE',
                    'two_letter_only' => true,
                ],
                'de_DE',
            ],
            'a two-letter language tag with extlang, script, and region results in the primary subtag with script and region' => [
                [
                    'http_accept_language' => 'de-ger-Latn-DE',
                    'use_script_subtag' => true,
                    'two_letter_only' => true,
                ],
                'de_Latn_DE',
            ],
            'a three-letter language tag without two_letter_only option results in default' => [
                [
                    'http_accept_language' => 'sgn',
                    'two_letter_only' => true,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag without two_letter_only option with extlang results in default' => [
                [
                    'http_accept_language' => 'sgn-ase',
                    'two_letter_only' => true,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag without two_letter_only option with script results in default' => [
                [
                    'http_accept_language' => 'sgn-Latn',
                    'two_letter_only' => true,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag without two_letter_only option with region results in default' => [
                [
                    'http_accept_language' => 'sgn-US',
                    'two_letter_only' => true,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag without two_letter_only option with extlang, script, and region results in default' => [
                [
                    'http_accept_language' => 'sgn-ase-Latn-US',
                    'two_letter_only' => true,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag with two_letter_only option results in the primary subtag' => [
                [
                    'http_accept_language' => 'sgn',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a three-letter language tag with two_letter_only option with extlang results in the primary subtag' => [
                [
                    'http_accept_language' => 'sgn-ase',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a three-letter language tag with two_letter_only option with script results in the primary subtag with script' => [
                [
                    'http_accept_language' => 'sgn-Latn',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a three-letter language tag with two_letter_only option with region results in the primary subtag with region' => [
                [
                    'http_accept_language' => 'sgn-US',
                    'two_letter_only' => false,
                ],
                'sgn_US',
            ],
            'a three-letter language tag with two_letter_only option with extlang, script, and region results in the primary subtag with script and region' => [
                [
                    'http_accept_language' => 'sgn-ase-Latn-US',
                    'two_letter_only' => false,
                ],
                'sgn_US',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentRequestHeadersWithMalformedValues
     */
    public function it_can_retrieve_a_preferred_language_when_the_header_is_malformed(
        array $options,
        string $expected
    ): void {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public static function provideDifferentRequestHeadersWithMalformedValues(): array
    {
        return [
            'one empty language tag results in default' => [
                ['http_accept_language' => ''],
                self::DEFAULT_LANGUAGE,
            ],
            'one empty language tag with empty quality tag results in default' => [
                ['http_accept_language' => ';q='],
                self::DEFAULT_LANGUAGE,
            ],
            'two empty languages tag results in default' => [
                ['http_accept_language' => ','],
                self::DEFAULT_LANGUAGE,
            ],
            'two empty languages tag with empty quality tag results in default' => [
                ['http_accept_language' => ',;q='],
                self::DEFAULT_LANGUAGE,
            ],
            'one language with quality tag with a wrong digit results in default' => [
                ['http_accept_language' => 'es;q=5'],
                self::DEFAULT_LANGUAGE,
            ],
            'one language with quality tag with a wrong value results in default' => [
                ['http_accept_language' => 'es;q=dd'],
                self::DEFAULT_LANGUAGE,
            ],
            'two languages with wrong quality order result to the language with the highest quality' => [
                ['http_accept_language' => 'en;q=0.3,es;q=1'],
                'es',
            ],
            'one language with two quality values in a row results in default' => [
                ['http_accept_language' => 'fr;q=0.5;q=0.3'],
                self::DEFAULT_LANGUAGE,
            ],
            'two languages with a semicolon as a separator results in default' => [
                ['http_accept_language' => 'de;en;q=0.5'],
                self::DEFAULT_LANGUAGE,
            ],
            'three languages without qualities results in the first language' => [
                ['http_accept_language' => 'de,en,es'],
                'de',
            ],
        ];
    }

    /** @test */
    public function it_can_retrieve_a_preferred_language_when_the_header_quality_parameter_is_uppercase(): void
    {
        // The quality parameter is case-insensitive. See RFC 7231, Section 5.3.1.
        $options = [
            'http_accept_language' => 'en,es;Q=0.9,fr;Q=0.8',
            'accepted_languages' => ['fr'],
        ];

        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('fr', $service->getPreferredLanguage());
    }

    /** @test */
    public function it_can_retrieve_a_preferred_language_when_the_header_quality_parameter_is_empty(): void
    {
        // This is an exceptional case where we want to handle an empty quality parameter value.
        $options = [
            'http_accept_language' => 'fr;q=,en;q=,gr',
            'accepted_languages' => ['fr', 'en'],
        ];

        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('fr', $service->getPreferredLanguage());
        $this->assertSame(1, $service->getQuality());
    }

    /** @test */
    public function it_cannot_log_activity_by_default(): void
    {
        $options = [
            'http_accept_language' => 'fr-CH,fr;q=0.9,en;q=0.8,de;q=0.7,*;q=0.5',
            'accepted_languages' => ['de', 'en'],
            'separator' => '_',
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())
            ->method('info');

        $service = new AcceptLanguage($options);
        $service->useLogger($loggerMock);
        $service->process();
    }

    /** @test */
    public function it_can_log_a_language_when_log_activity_is_enabled(): void
    {
        $options = [
            'http_accept_language' => 'fr-CH',
            'accepted_languages' => [],
            'separator' => '_',
            'log_activity' => true,
            'log_only' => ['retrieve_header'],
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with($this->stringContains('fr-CH'));

        $service = new AcceptLanguage($options);
        $service->useLogger($loggerMock);
        $service->process();
    }

    /** @test */
    public function it_can_log_a_valid_language_all_of_the_stages_when_log_activity_is_enabled(): void
    {
        $options = [
            'http_accept_language' => 'fr-CH,fr;q=0.9,en;q=0.8,de;q=0.7,*;q=0.5',
            'accepted_languages' => ['de', 'en'],
            'separator' => '_',
            'log_activity' => true,
        ];
        $expectedArguments = [
            'fr-CH',
            'fr_CH;valid,fr;valid',
            'fr_CH;q=1,fr;q=0.9',
            'de,en',
            'en;q=0.8,de;q=0.7',
            'en',
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $matcher = $this->exactly(count($expectedArguments));
        $loggerMock->expects($matcher)
            ->method('info')
            ->with(
                $this->callback(function ($param) use ($expectedArguments, $matcher) {
                    $needle = $expectedArguments[$this->resolveInvocations($matcher) - 1];
                    $this->assertStringContainsString($needle, $param);
                    return true;
                })
            );

        $service = new AcceptLanguage($options);
        $service->useLogger($loggerMock);
        $service->process();
    }

    /** @test */
    public function it_can_log_an_invalid_language_all_of_the_stages_when_log_activity_is_enabled(): void
    {
        $options = [
            'http_accept_language' => 'completely wrong',
            'accepted_languages' => ['de', 'en'],
            'separator' => '_',
            'log_activity' => true,
        ];
        $expectedArguments = [
            'completely wrong',
            'completely wrong;invalid',
            'empty',
            '',
            'empty',
            'en',
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $matcher = $this->exactly(count($expectedArguments));
        $loggerMock->expects($matcher)
            ->method('info')
            ->with(
                $this->callback(function ($param) use ($expectedArguments, $matcher) {
                    $needle = $expectedArguments[$this->resolveInvocations($matcher) - 1];
                    $this->assertStringContainsString($needle, $param);
                    return true;
                })
            );

        $service = new AcceptLanguage($options);
        $service->useLogger($loggerMock);
        $service->process();
    }

    private function resolveInvocations(\PHPUnit\Framework\MockObject\Rule\InvocationOrder $matcher): int
    {
        if (method_exists($matcher, 'numberOfInvocations')) {
            return $matcher->numberOfInvocations();
        }

        if (method_exists($matcher, 'getInvocationCount')) {
            return $matcher->getInvocationCount();
        }

        $this->fail('Cannot count the number of invocations.');
    }
}
