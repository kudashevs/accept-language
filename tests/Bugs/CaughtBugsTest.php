<?php

namespace Kudashevs\AcceptLanguage\Tests\Bugs;

use Kudashevs\AcceptLanguage\AcceptLanguage;
use Kudashevs\AcceptLanguage\Facades\AcceptLanguage as AcceptLanguageFacade;
use Kudashevs\AcceptLanguage\Tests\ExtendedTestCase;

class CaughtBugsTest extends ExtendedTestCase
{
    /**
     * @test
     */
    public function it_can_handle_a_bug_in_the_retrieve_acceptable_languages_intersection()
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

    /**
     * @test
     */
    public function it_can_handle_a_bug_in_the_parse_header()
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

    /** @test */
    public function it_can_apply_boolean_options_using_the_initial_config()
    {
        /*
         * Bug found: 27.08.2023
         * Details: The AcceptLanguageServiceProvider class was not able to apply the boolean options due to
         * a wrong assumption in the use of array_filter() function with the initial options. For more details
         * @see AcceptLanguageServiceProvider::getInitialConfig()
         */
        config()->set('accept-language.default_language', 'fr-Latn-CH');
        config()->set('accept-language.use_script_subtag', false);
        config()->set('accept-language.use_region_subtag', false);
        $language = AcceptLanguageFacade::getLanguage();

        $this->assertNotEmpty($language);
        $this->assertSame('fr', $language);
    }
}
