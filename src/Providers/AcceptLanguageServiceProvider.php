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
        $this->app->singleton(AcceptLanguage::class, function () {
            return new AcceptLanguage($this->getConfig());
        });

        $this->app->alias(AcceptLanguage::class, 'acceptlanguage');
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        $config = [
            'default_language' => config('app.locale', ''),
            'accepted_languages' => config('app.accepted_locales', []),
        ];

        return array_filter($config);
    }
}
