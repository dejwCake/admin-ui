<?php

declare(strict_types=1);

namespace Brackets\AdminUI;

use Brackets\AdminUI\Console\Commands\AdminUIInstall;
use Brackets\AdminUI\Providers\ViewComposerProvider;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

final class AdminUIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'brackets/admin-ui');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'brackets/admin-ui');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        if ($this->app->runningInConsole()) {
            $this->publish();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/wysiwyg-media.php', 'wysiwyg-media');

        $this->app->alias('image', ImageManager::class);

        $this->app->register(ViewComposerProvider::class);

        $this->commands([
            AdminUIInstall::class,
        ]);
    }

    private function publish(): void
    {
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__ . '/../config/wysiwyg-media.php' => $this->app->configPath('wysiwyg-media.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/brackets/admin-ui'),
        ], 'lang');

        if (!glob($this->app->databasePath('migrations/*_create_wysiwyg_media_table.php'))) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_wysiwyg_media_table.php'
                => sprintf(
                    '%s/%s_create_wysiwyg_media_table.php',
                    $this->app->databasePath('migrations'),
                    $timestamp,
                ),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . '/../install-stubs/resources/js/admin' => $this->app->resourcePath('js/admin'),
            __DIR__ . '/../install-stubs/resources/css/admin' => $this->app->resourcePath('css/admin'),
        ], 'assets');

        $this->publishes([
            __DIR__ . '/../install-stubs/resources/views' => $this->app->resourcePath('views'),
        ], 'views');
    }
}
