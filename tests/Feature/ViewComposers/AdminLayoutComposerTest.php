<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature\ViewComposers;

use Brackets\AdminUI\Tests\TestCase;
use Brackets\AdminUI\ViewComposers\AdminLayoutComposer;
use Illuminate\Contracts\View\Factory;

final class AdminLayoutComposerTest extends TestCase
{
    public function testComposesAppLocaleFromConfig(): void
    {
        $this->app['config']->set('app.locale', 'sk');

        $data = $this->composeAndGetData();

        self::assertSame('sk', $data['appLocale']);
    }

    public function testDefaultsAppLocaleToEnWhenNotSet(): void
    {
        $this->app['config']->set('app.locale', null);

        $data = $this->composeAndGetData();

        self::assertSame('en', $data['appLocale']);
    }

    public function testComposesCsrfTokenAsEmptyStringWhenNoSession(): void
    {
        $data = $this->composeAndGetData();

        self::assertArrayHasKey('csrfToken', $data);
        self::assertSame('', $data['csrfToken']);
    }

    /**
     * @return array<string, string>
     */
    private function composeAndGetData(): array
    {
        $composer = $this->app->make(AdminLayoutComposer::class);

        $viewFactory = $this->app->make(Factory::class);
        $view = $viewFactory->make('brackets/admin-ui::admin.layout.master');

        $composer->compose($view);

        return $view->getData();
    }
}
