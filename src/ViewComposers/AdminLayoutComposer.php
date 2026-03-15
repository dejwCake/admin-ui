<?php

declare(strict_types=1);

namespace Brackets\AdminUI\ViewComposers;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Session\TokenMismatchException;
use Illuminate\View\View;

final readonly class AdminLayoutComposer
{
    public function __construct(private Config $config)
    {
    }

    public function compose(View $view): void
    {
        $view->with('appLocale', $this->config->get('app.locale', 'en'));

        try {
            $view->with('csrfToken', csrf_token());
        } catch (TokenMismatchException) {
            $view->with('csrfToken', '');
        }
    }
}
