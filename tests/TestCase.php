<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests;

use Brackets\AdminUI\AdminUIServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        // let's define simple routes
        $this->app['router']->get('/admin/test/index', static fn () => view('admin.test.index'));

        $filesystem = $this->app->make(Filesystem::class);
        $filesystem->copyDirectory(__DIR__ . '/fixtures/public', $this->app->publicPath());
        $filesystem->copyDirectory(__DIR__ . '/fixtures/resources/views', $this->app->resourcePath('views'));
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
