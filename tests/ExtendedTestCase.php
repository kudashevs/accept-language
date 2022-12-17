<?php

namespace Kudashevs\AcceptLanguage\Tests;

use Orchestra\Testbench\TestCase;

class ExtendedTestCase extends TestCase
{
    /**
     * Load Laravel share service provider
     *
     * @param \Illuminate\Foundation\Application $application
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return ['Kudashevs\AcceptLanguage\Providers\AcceptLanguageServiceProvider'];
    }
}
