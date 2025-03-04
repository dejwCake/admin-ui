<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests;

use Brackets\AdminUI\AdminUIServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\BrowserKit\TestCase as OrchestraBrowser;

abstract class TestCase extends OrchestraBrowser
{
    public function setUp(): void
    {
        parent::setUp();

        // let's define simple routes
        $this->app['router']->get('/admin/test/index', static fn () => view('admin.test.index'));

        File::copyDirectory(__DIR__ . '/fixtures/public', public_path());
        File::copyDirectory(__DIR__ . '/fixtures/resources/views', resource_path('views'));
    }

    /**
     * @param Application $app
     * @return class-string[]
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function getPackageProviders($app): array
    {
        return [
            AdminUIServiceProvider::class,
        ];
    }
}
