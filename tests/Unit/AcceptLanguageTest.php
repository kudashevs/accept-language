<?php

namespace Kudashevs\AcceptLanguage\Tests\Unit;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Exceptions\InvalidOptionArgumentException;
use PHPUnit\Framework\TestCase;

class AcceptLanguageTest extends TestCase
{
    protected const DEFAULT_LANGUAGE = 'en';

    /**
     * @test
     * @dataProvider provideDifferentWrongOptions
     */
    public function it_can_throw_exception_when_an_option_of_a_wrong_type(array $option)
    {
        $this->expectException(InvalidOptionArgumentException::class);
        $this->expectExceptionMessage('The option "' . key($option) . '" has a wrong value type');

        new AcceptLanguage($option);
    }

    public function provideDifferentWrongOptions(): array
    {
        return [
            'an http_accept_language option with a wrong value' => [
                ['http_accept_language' => null],
            ],
            'an default_language option with a wrong value' => [
                ['default_language' => null],
            ],
            'an accepted_languages option with a wrong value' => [
                ['accepted_languages' => null],
            ],
            'an two_letter_only option with a wrong value' => [
                ['two_letter_only' => null],
            ],
            'an separator option with a wrong value' => [
                ['separator' => null],
            ],
        ];
    }

    /** @test */
    public function it_can_retain_the_original_header()
    {
        $service = new AcceptLanguage([
            'http_accept_language' => 'en-US,en;q=0.5',
        ]);
        $service->process();

        $this->assertEquals('en-US,en;q=0.5', $service->getHeader());
    }

    /**
     * @test
     */
    public function it_can_retrieve_a_language()
    {
        $service = new AcceptLanguage();
        $service->process();

        $this->assertNotEmpty($service->getPreferredLanguage());
        $this->assertNotEmpty($service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_default_language_when_no_options_and_no_header_are_provided()
    {
        $service = new AcceptLanguage();
        $service->process();

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_preferred_language_from_options()
    {
        $options = ['default_language' => 'de'];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($options['default_language'], $service->getPreferredLanguage());
        $this->assertSame($options['default_language'], $service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_default_language_when_a_language_is_not_listed_in_accepted_languages()
    {
        $options = [
            'http_accept_language' => 'pp',
            'accepted_languages' => ['en', 'de', 'fr'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getLanguage());
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_preferred_language_when_a_language_is_listed_in_accepted_languages()
    {
        $options = [
            'default_language' => 'es',
            'accepted_languages' => ['en', 'de', 'es'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($options['default_language'], $service->getPreferredLanguage());
        $this->assertSame($options['default_language'], $service->getLanguage());
    }

    /**
     * @test
     * @dataProvider provideDifferentDefaultLanguageOptions
     */
    public function it_can_format_the_default_language_option_according_to_the_settings(
        array $options,
        string $expected
    ) {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expected, $service->getPreferredLanguage());
        $this->assertSame($expected, $service->getLanguage());
    }

    public function provideDifferentDefaultLanguageOptions()
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
     * @dataProvider provideDifferentAcceptedLanguagesOptions
     */
    public function it_can_format_the_accepted_languages_option_according_to_the_settings(
        array $options,
        string $expected
    ) {
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame($expected, $service->getPreferredLanguage());
        $this->assertSame($expected, $service->getLanguage());
    }

    public function provideDifferentAcceptedLanguagesOptions()
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
    public function it_can_retrieve_the_preferred_language(array $options, string $expected)
    {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentRequestHeaderValues(): array
    {
        return [
            'any language tag results in default' => [
                ['http_accept_language' => '*'],
                self::DEFAULT_LANGUAGE,
            ],
            'any language tag with highest quality results in default' => [
                ['http_accept_language' => '*,de;q=0.7'],
                self::DEFAULT_LANGUAGE,
            ],
            'any language tag and language tag with equal quality results in default' => [
                ['http_accept_language' => '*,es,de;q=0.7'],
                self::DEFAULT_LANGUAGE,
            ],
            'any language tag and language tag with equal quality results in the language' => [
                ['http_accept_language' => 'es,*,de;q=0.7'],
                'es',
            ],
            'a two-letter language tag results in the language' => [
                ['http_accept_language' => 'fr'],
                'fr',
            ],
            'a three-letter language tag results in default' => [
                ['http_accept_language' => 'sgn'],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag with option results in default' => [
                [
                    'http_accept_language' => 'sgn',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a four letters language tag results in default' => [
                ['http_accept_language' => 'test'],
                self::DEFAULT_LANGUAGE,
            ],
            'a two-letter language tag with region results in the language' => [
                ['http_accept_language' => 'en-us'],
                'en_US',
            ],
            'a two-letter language tag with script and region results in the language' => [
                ['http_accept_language' => 'zh-Hant-HK'],
                'zh_HK',
            ],
            'a two-letter language tag with 0 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=0'],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag with 0 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0',
                    'two_letter_only' => false,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'a two-letter language tag with 0.001 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=0.001'],
                'de',
            ],
            'a three-letter language tag with 0.001 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0.001',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a two-letter language tag with quality language tag results in the language' => [
                ['http_accept_language' => 'de;q=0.5'],
                'de',
            ],
            'a three-letter language tag with quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0.5',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a two-letter language tag with 0.999 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=0.999'],
                'de',
            ],
            'a three-letter language tag with 0.999 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=0.999',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a two-letter language tag with 1 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=1'],
                'de',
            ],
            'a three-letter language tag with 1 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=1',
                    'two_letter_only' => false,
                ],
                'sgn',
            ],
            'a two-letter language tag with 1.001 quality language tag results in default' => [
                ['http_accept_language' => 'de;q=1.001'],
                self::DEFAULT_LANGUAGE,
            ],
            'a three-letter language tag with 1.001 quality language tag results in the language' => [
                [
                    'http_accept_language' => 'sgn;q=1.001',
                    'two_letter_only' => false,
                ],
                self::DEFAULT_LANGUAGE,
            ],
            'a four letters language tag with quality language tag results in default' => [
                ['http_accept_language' => 'test;q=0.5'],
                self::DEFAULT_LANGUAGE,
            ],
            'a sequence of language tags results in the language' => [
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
                'de',
            ],
            'an example all lowercase results in the language' => [
                ['http_accept_language' => 'de,en-us;q=0.7,en;q=0.3'],
                'de',
            ],
            'an example part uppercase results in the language' => [
                ['http_accept_language' => 'de-DE,de;q=0.9,en;q=0.8'],
                'de_DE',
            ],
            'the mozilla Accept-Language page examples basic results in the language' => [
                ['http_accept_language' => 'de'],
                'de',
            ],
            'the mozilla Accept-Language page examples hyphenated results in the language' => [
                ['http_accept_language' => 'de-CH'],
                'de_CH',
            ],
            'the mozilla Accept-Language page examples complex results in the language' => [
                ['http_accept_language' => 'en-US,en;q=0.5'],
                'en_US',
            ],
            'the mozilla Accept-Language page examples complex with space results in the language' => [
                ['http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'],
                'fr_CH',
            ],
            'RFC 2616 14.4 Accept-Language example results in the language' => [
                ['http_accept_language' => 'da, en-gb;q=0.8, en;q=0.7'],
                'da',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideDifferentAcceptedLanguagesValues
     */
    public function it_can_retrieve_the_preferred_language_when_the_accepted_languages_is_set(
        array $options,
        string $expected
    ) {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentAcceptedLanguagesValues(): array
    {
        return [
            'a language that intersects with accepted_languages results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['de'],
                ],
                'de',
            ],
            'a language that intersects with accepted_languages results in the accepted language when it is of quality 1' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['de', 'fr'],
                ],
                'fr',
            ],
            'a language that intersects with accepted_languages results in the accepted language when it is of quality below 1' => [
                [
                    'http_accept_language' => 'de;q=0.7,fr;q=0.333,es;q=0.333',
                    'accepted_languages' => ['en', 'es'],
                ],
                'es',
            ],
            'a language that intersects with accepted_languages with hyphen separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr-CH'],
                ],
                'fr_CH',
            ],
            'a language that intersects with accepted_languages with underscore separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr_CH'],
                    'separator' => '_',
                ],
                'fr_CH',
            ],
            'a language that intersects with accepted_languages with tilde separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr~CH'],
                    'separator' => '~',
                ],
                'fr~CH',
            ],
            'a language that intersects with accepted_languages formatted with hyphen separator and mixed letters results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-Latn-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr-lAtn-Ch'],
                    'use_script_subtag' => true,
                ],
                'fr_Latn_CH',

            ],
            'a language that intersects with accepted_languages formatted with underscore separator and mixed letters results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-Latn-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr_lAtn_Ch'],
                    'use_script_subtag' => true,
                    'separator' => '_',
                ],
                'fr_Latn_CH',

            ],
            'a language that intersects with accepted_languages formatted with tilde separator and mixed letters results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-Latn-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr~lAtn~Ch'],
                    'use_script_subtag' => true,
                    'separator' => '~',
                ],
                'fr~Latn~CH',

            ],
            'a language that intersects with accepted_languages and a separator results in the accepted language' => [
                [
                    'http_accept_language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5',
                    'accepted_languages' => ['fr-CH'],
                    'use_script_subtag' => true,
                    'separator' => '_',
                ],
                'fr_CH',
            ],
            'RFC 2616 14.4 Accept-Language example returns the accepted language when it is of quality 1' => [
                [
                    'http_accept_language' => 'da, en-gb, fr;q=0.8, en;q=0.7',
                    'accepted_languages' => ['en'],
                ],
                'en',
            ],
            'RFC 2616 14.4 Accept-Language example returns the accepted language when it is of quality below 1' => [
                [
                    'http_accept_language' => 'da, en-gb, fr;q=0.8, en;q=0.7',
                    'accepted_languages' => ['fr'],
                ],
                'fr',
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
    public function it_can_retrieve_the_preferred_language_with_different_options(array $options, string $expected)
    {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentTwoLetterOnlyOptions(): array
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
        ];
    }

    public function provideDifferentUseExtlangScriptRegionSubtagOptions(): array
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

    public function provideDifferentMatchingOptions(): array
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

    public function provideDifferentSeparatorOptions(): array
    {
        return [
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
    public function it_can_retrieve_the_preferred_language_of_different_length(array $options, string $expected)
    {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentRequestHeadersWithDifferentLanguageLetterLength(): array
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
    public function it_can_retrieve_the_preferred_language_when_the_header_is_malformed(
        array $options,
        string $expected
    ) {
        $service = new AcceptLanguage($options);
        $service->process();
        $result = $service->getPreferredLanguage();

        $this->assertSame($expected, $result);
    }

    public function provideDifferentRequestHeadersWithMalformedValues(): array
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
        ];
    }

    /**
     * @test
     */
    public function it_can_retrieve_the_preferred_language_when_the_header_quality_parameter_is_empty()
    {
        /**
         * This is an exceptional case where we want to handle an empty quality parameter value.
         * @see LanguageQualityNormalizer::normalizeWithFallback()
         */
        $options = [
            'http_accept_language' => 'fr;q=,en;q=,gr',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('fr', $service->getPreferredLanguage());
    }

    /**
     * Caught bugs section.
     */

    /**
     * @test
     */
    public function it_can_handle_a_bug_in_the_retrieve_acceptable_languages_intersection()
    {
        /**
         * Bug found: 14.02.2021
         * Details: The returned language doesn't follow the order from an HTTP Accept-Language header value.
         * The bug is in the retrieveAcceptableLanguagesIntersection() method and is related to a wrong order
         * of array_intersect_key() parameters.
         */
        $options = [
            'http_accept_language' => 'fr-CH,fr;q=0.8,en-US;q=0.5,en;q=0.3',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('fr', $service->getPreferredLanguage());
    }

    /**
     * @test
     */
    public function it_can_handle_a_bug_in_the_parse_header()
    {
        /**
         * Bug found: 13.01.2022
         * Details: The package crashes with a message array_combine(): Both parameters should have an equal number of elements.
         * The bug happens in the parseHeaderValue() method due to the specific HTTP Accept-Language header which is sent
         * by PetalBot browser running on Android OS.
         */
        $options = [
            'http_accept_language' => ';q=;q=0.3',
            'accepted_languages' => ['fr', 'en'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame(self::DEFAULT_LANGUAGE, $service->getPreferredLanguage());
    }
}