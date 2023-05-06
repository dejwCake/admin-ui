<?php

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
        $this->app['router']->get('/admin/test/index', function(){
            return view('admin.test.index');
        });

        File::copyDirectory(__DIR__.'/fixtures/public', public_path());
        File::copyDirectory(__DIR__.'/fixtures/resources/views', resource_path('views'));
    }

    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            AdminUIServiceProvider::class,
        ];
    }
}
