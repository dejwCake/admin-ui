<?php

declare(strict_types=1);

namespace Brackets\AdminUI\ViewComposers;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\View;

final readonly class AdminLayoutComposer
{
    public function __construct(private Config $config, private ?Session $session = null)
    {
    }

    public function compose(View $view): void
    {
        $view->with('appLocale', $this->config->get('app.locale') ?? 'en');
        $view->with('csrfToken', $this->session?->token() ?? '');
    }
}
