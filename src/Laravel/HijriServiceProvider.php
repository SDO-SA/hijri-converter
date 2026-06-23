<?php

declare(strict_types=1);

namespace SDOSA\Laravel;

use Illuminate\Support\ServiceProvider;
use SDOSA\Locales\LocaleRegistry;

/**
 * Optional Laravel glue. The core API (SDOSA\Hijri) is static and needs no
 * binding; this provider only wires config, the default locale, Carbon macros,
 * and config publishing.
 */
final class HijriServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'hijri');
    }

    public function boot(): void
    {
        $config = $this->app['config']->get('hijri', []);
        LocaleRegistry::setDefault($config['default_locale'] ?? 'ar');

        CarbonMacros::register();

        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => $this->app->configPath('hijri.php')], 'hijri-config');
        }
    }

    private function configPath(): string
    {
        return __DIR__ . '/../../config/hijri.php';
    }
}
