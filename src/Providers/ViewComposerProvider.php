<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Providers;

use Brackets\AdminUI\ViewComposers\AdminHeaderComposer;
use Brackets\AdminUI\ViewComposers\AdminLayoutComposer;
use Brackets\AdminUI\ViewComposers\WysiwygUploadUrlComposer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;

final class ViewComposerProvider extends ServiceProvider
{
    public function boot(): void
    {
        $viewFactory = $this->app->get(Factory::class);
        $viewFactory->composer('*', WysiwygUploadUrlComposer::class);
        $viewFactory->composer('brackets/admin-ui::admin.layout.master', AdminLayoutComposer::class);
        $viewFactory->composer('brackets/admin-ui::admin.partials.header', AdminHeaderComposer::class);
    }

    public function register(): void
    {
        //do nothing
    }
}
