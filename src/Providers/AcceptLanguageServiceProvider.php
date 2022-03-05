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
        $this->app->singleton(AcceptLanguage::class, function ($app) {
            return new AcceptLanguage($this->getConfig($app));
        });

        $this->app->alias(AcceptLanguage::class, 'acceptlanguage');
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
        $config = [
            'default_language' => config('app.locale', ''),
            'accepted_languages' => config('app.accepted_locales', []),
        ];

        return array_filter($config);
    }
}
