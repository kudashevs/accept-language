<?php

namespace Kudashevs\AcceptLanguage\Tests\Bugs;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Facades\AcceptLanguage as AcceptLanguageFacade;
use Kudashevs\AcceptLanguage\Tests\ExtendedTestCase;
use PHPUnit\Framework\Attributes\Test;

class CaughtBugsTest extends ExtendedTestCase
{
    #[Test]
    public function it_can_handle_a_bug_in_the_retrieve_acceptable_languages_intersection(): void
    {
        /*
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

    #[Test]
    public function it_can_handle_a_bug_in_the_parse_header(): void
    {
        /*
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

        $this->assertSame('en', $service->getPreferredLanguage());
    }

    #[Test]
    public function it_can_apply_boolean_options_using_the_initial_config(): void
    {
        /*
         * Bug found: 27.08.2023
         * Details: The AcceptLanguageServiceProvider class was not able to apply the boolean options due to
         * a wrong assumption in the use of array_filter() function with the initial options. For more details
         * @see \Kudashevs\AcceptLanguage\Providers\AcceptLanguageServiceProvider::getInitialConfig()
         */
        config()->set('accept-language.default_language', 'fr-Latn-CH');
        config()->set('accept-language.use_script_subtag', false);
        config()->set('accept-language.use_region_subtag', false);
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr', $language);
    }

    #[Test]
    public function it_can_retrieve_the_first_accepted_language_from_a_long_header_without_quality_values(): void
    {
        /*
         * Bug found: 02.11.2026
         * Details: The fallback quality was decreased by 0.1 for each language range without a quality value.
         * Starting from the 11th language range, the fallback quality became invalid, so the quality value
         * was reset to 1, and the language took precedence over all the previous ones (except the first one).
         * Update: the position-based fallback quality was replaced with the default quality of 1 for all ranges
         * without a quality value (RFC 9110, Section 12.4.2). The header order is retained by the stable sorting.
         * @see \Kudashevs\AcceptLanguage\AcceptLanguage::retrieveRawLanguages()
         */
        $options = [
            'http_accept_language' => 'aa,bb,cc,dd,ee,ff,gg,hh,ii,jj,kk,ll',
            'accepted_languages' => ['bb', 'kk', 'll'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('bb', $service->getPreferredLanguage());
        $this->assertSame(1, $service->getPreferredLanguageQuality());
    }

    #[Test]
    public function it_can_retain_the_header_order_of_languages_with_the_same_default_quality(): void
    {
        /*
         * Bug found: 02.11.2026
         * Details: All the language ranges starting from the 11th one got the minimum fallback quality.
         * They must keep the order of the HTTP Accept-Language header among themselves.
         * Update: all ranges without a quality value get the default quality of 1 (RFC 9110, Section 12.4.2).
         * Languages with the same quality keep the order of the HTTP Accept-Language header (stable sorting).
         */
        $options = [
            'http_accept_language' => 'aa,bb,cc,dd,ee,ff,gg,hh,ii,jj,kk,ll',
            'accepted_languages' => ['ll', 'kk'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('kk', $service->getPreferredLanguage());
        $this->assertSame(1, $service->getPreferredLanguageQuality());
    }
}
