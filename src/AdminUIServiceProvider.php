<?php

declare(strict_types=1);

namespace Brackets\AdminUI;

use Brackets\AdminUI\Console\Commands\AdminUIInstall;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AdminUIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            AdminUIInstall::class,
        ]);

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'brackets/admin-ui');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'brackets/admin-ui');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        if ($this->app->runningInConsole()) {
            $time = date('His', time());
            $this->publishes([
                __DIR__ . '/../install-stubs/resources/js/admin' => resource_path('js/admin'),
                __DIR__ . '/../install-stubs/resources/sass/admin' => resource_path('sass/admin'),
            ], 'assets');

            $this->publishes([
                __DIR__ . '/../install-stubs/resources/views' => resource_path('views'),
            ], 'views');

            $this->publishes([
                __DIR__ . '/../install-stubs/config/wysiwyg-media.php' => config_path('wysiwyg-media.php'),
            ], 'config');

            if (!File::exists(base_path('webpack.mix.js'))) {
                $this->publishes([
                    __DIR__ . '/../install-stubs/webpack.mix.js' => base_path('webpack.mix.js'),
                ], 'webpack');
            }

            if (!glob(base_path('database/migrations/*_create_wysiwyg_media_table.php'))) {
                $this->publishes([
                    __DIR__ . '/../install-stubs/database/migrations/create_wysiwyg_media_table.php' => database_path(
                        'migrations',
                    ) . '/2025_01_01_' . $time . '_create_wysiwyg_media_table.php',
                ], 'migrations');
            }
        }
    }

    public function register(): void
    {
        //do nothing
    }
}
