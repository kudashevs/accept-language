<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Providers;

use Illuminate\Support\ServiceProvider;
use Kudashevs\AcceptLanguage\AcceptLanguage;
use Psr\Log\LoggerInterface;

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
            $service = new AcceptLanguage($this->getInitialConfig());
            $service->useLogger($this->getLogger());
            $service->process();

            return $service;
        });

        $this->app->alias(AcceptLanguage::class, 'acceptlanguage');
    }

    /**
     * @return array<string, string|array>
     */
    private function getInitialConfig(): array
    {
        $fallbackLanguage = config('app.locale', 'en');

        $config = [
            'default_language' => config('accept-language.default_language', $fallbackLanguage),
            'accepted_languages' => config('accept-language.accepted_languages', []),
            'log_activity' => config('accept-language.log_activity', 'false'),
        ];

        return array_filter($config);
    }

    private function getLogger(): LoggerInterface
    {
        return $this->app['log'] ?? $this->app->make(LoggerInterface::class);
    }
}
