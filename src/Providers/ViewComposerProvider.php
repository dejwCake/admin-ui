<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Providers;

use Brackets\AdminUI\ViewComposers\WysiwygUploadUrlComposer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;

class ViewComposerProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function boot(): void
    {
        $viewFactory = $this->app->get(Factory::class);
        $viewFactory->composer('*', WysiwygUploadUrlComposer::class);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //do nothing
    }
}
