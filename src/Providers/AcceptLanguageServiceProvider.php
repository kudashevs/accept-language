<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\Providers;

use Illuminate\Support\ServiceProvider;
use Kudashevs\AcceptLanguage\AcceptLanguage;
use Psr\Log\LoggerInterface;

class AcceptLanguageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/accept-language.php' => config_path('accept-language.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(AcceptLanguage::class, function () {
            $service = new AcceptLanguage($this->getInitialConfig());

            return tap($service, function (AcceptLanguage $service) {
                if ($this->shouldLogEvents($this->getInitialConfig())) {
                    $service->useLogger($this->getLogger());
                }

                $service->process();
            });
        });
        $this->app->alias(AcceptLanguage::class, 'acceptlanguage');

        $this->mergeConfigFrom(__DIR__ . '/../../config/accept-language.php', 'accept-language');
    }

    /**
     * @return array{
     *     http_accept_language: string,
     *     default_language: string,
     *     accepted_languages: array<array-key, string>,
     *     exact_match_only: bool,
     *     two_letter_only: bool,
     *     use_extlang_subtag: bool,
     *     use_script_subtag: bool,
     *     use_region_subtag: bool,
     *     separator: string,
     *     log_activity: bool,
     *     log_level: string,
     *     log_only: array<array-key, string>,
     * }
     */
    private function getInitialConfig(): array
    {
        $fallbackLanguage = config('app.locale', 'en');

        return [
            'http_accept_language' => config('accept-language.http_accept_language', ''),
            'default_language' => config('accept-language.default_language', $fallbackLanguage),
            'accepted_languages' => config('accept-language.accepted_languages', []),
            'exact_match_only' => config('accept-language.exact_match_only', false),
            'two_letter_only' => config('accept-language.two_letter_only', true),
            'use_extlang_subtag' => config('accept-language.use_extlang_subtag', false),
            'use_script_subtag' => config('accept-language.use_script_subtag', false),
            'use_region_subtag' => config('accept-language.use_region_subtag', true),
            'separator' => config('accept-language.separator', '_'),
            'log_activity' => config('accept-language.log_activity', false),
            'log_level' => config('accept-language.log_level', 'info'),
            'log_only' => config('accept-language.log_only', []),
        ];
    }

    /**
     * @param array{log_activity: bool} $options
     * @return bool
     */
    private function shouldLogEvents(array $options): bool
    {
        return isset($options['log_activity']) && $options['log_activity'] === true;
    }

    private function getLogger(): LoggerInterface
    {
        return $this->app['log'] ?? $this->app->make(LoggerInterface::class);
    }
}
