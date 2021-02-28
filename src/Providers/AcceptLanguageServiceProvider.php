<?php

namespace Kudashevs\AcceptLanguage\Providers;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Kudashevs\AcceptLanguage\AcceptLanguage;

class AcceptLanguageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('acceptlanguage', function ($app) {
            return (new AcceptLanguage($this->getConfig($app)));
        });

        $this->app->alias('acceptlanguage', AcceptLanguage::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * @param Container $app
     * @return array
     */
    private function getConfig(Container $app): array
    {
        return array_filter([
            'default_language' => $app['config']['app.locale'] ?? false,
            'accepted_languages' => $app['config']['app.accepted_locales'] ?? false,
        ]);
    }
}
