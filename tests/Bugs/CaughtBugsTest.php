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
    public function it_can_retain_the_order_of_a_long_header_without_quality_values(): void
    {
        /*
         * Bug found: 02.11.2026
         * Details: The fallback quality was decreased by 0.1 for each language range without a quality value.
         * Starting from the 11th language range, the fallback quality became invalid, so the quality value
         * was reset to 1, and the language took precedence over all the previous ones (except the first one).
         * @see \Kudashevs\AcceptLanguage\AcceptLanguage::retrieveRawLanguages()
         */
        $options = [
            'http_accept_language' => 'aa,bb,cc,dd,ee,ff,gg,hh,ii,jj,kk,ll',
            'accepted_languages' => ['bb', 'kk', 'll'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('bb', $service->getPreferredLanguage());
        $this->assertSame(0.9, $service->getPreferredLanguageQuality());
    }

    #[Test]
    public function it_can_retrieve_a_fallback_quality_without_floating_point_errors(): void
    {
        /*
         * Bug found: 02.11.2026
         * Details: The repeated subtraction of the fallback quality step accumulated floating point errors,
         * so the returned quality value was 0.7000000000000001 instead of 0.7.
         */
        $options = [
            'http_accept_language' => 'aa,bb,cc,dd',
            'accepted_languages' => ['dd'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('dd', $service->getPreferredLanguage());
        $this->assertSame(0.7, $service->getPreferredLanguageQuality());
    }

    #[Test]
    public function it_can_retain_the_order_of_languages_with_the_minimum_fallback_quality(): void
    {
        /*
         * Bug found: 02.11.2026
         * Details: All the language ranges starting from the 11th one get the minimum fallback quality.
         * They must keep the order of the HTTP Accept-Language header among themselves.
         */
        $options = [
            'http_accept_language' => 'aa,bb,cc,dd,ee,ff,gg,hh,ii,jj,kk,ll',
            'accepted_languages' => ['ll', 'kk'],
        ];
        $service = new AcceptLanguage($options);
        $service->process();

        $this->assertSame('kk', $service->getPreferredLanguage());
        $this->assertSame(0.001, $service->getPreferredLanguageQuality());
    }
}
