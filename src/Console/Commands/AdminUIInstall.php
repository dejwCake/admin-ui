<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Console\Commands;

use Brackets\AdminUI\Console\Support\PackageJsonEditor;
use Brackets\AdminUI\Console\Support\ViteConfigEditor;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

final class AdminUIInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'admin-ui:install';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Install a brackets/admin-ui package';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Application $app,
        private readonly ViteConfigEditor $viteConfigEditor,
        private readonly PackageJsonEditor $packageJsonEditor,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->info('Installing package brackets/admin-ui');

        $this->frontendAdjustments();

        $this->call('vendor:publish', [
            '--provider' => "Brackets\\AdminUI\\AdminUIServiceProvider",
        ]);

        $this->info('Package brackets/admin-ui installed');
    }

    /**
     * @throws FileNotFoundException
     */
    private function frontendAdjustments(): void
    {
        $this->adjustViteConfig();
        $this->adjustPackageJson();
    }

    /**
     * @throws FileNotFoundException
     */
    private function adjustViteConfig(): void
    {
        $viteConfigPath = $this->locateViteConfig();

        if ($viteConfigPath === null) {
            $viteConfigPath = $this->app->basePath('vite.config.js');
            $this->filesystem->copy(__DIR__ . '/../../../install-stubs/vite.config.js', $viteConfigPath);
            $this->info('Vite configuration created');

            return;
        }

        $original = $this->filesystem->get($viteConfigPath);
        $isTypeScript = str_ends_with($viteConfigPath, '.ts') || str_ends_with($viteConfigPath, '.mts')
            || str_ends_with($viteConfigPath, '.cts');
        $updated = $this->viteConfigEditor->installAdminUi($original, $isTypeScript);

        if ($original === $updated) {
            $this->info('Vite configuration already up to date, skipping');

            return;
        }

        $this->filesystem->put($viteConfigPath, $updated);
        $this->info('Vite configuration updated (' . basename($viteConfigPath) . ')');
    }

    /**
     * Locate the existing Vite config file, probing extensions in Vite's own resolution order.
     */
    private function locateViteConfig(): ?string
    {
        foreach (['js', 'mjs', 'ts', 'cjs', 'mts', 'cts'] as $extension) {
            $path = $this->app->basePath('vite.config.' . $extension);
            if ($this->filesystem->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @throws FileNotFoundException
     */
    private function adjustPackageJson(): void
    {
        $packageJsonFile = $this->app->basePath('package.json');
        $original = $this->filesystem->get($packageJsonFile);
        $updated = $this->packageJsonEditor->installAdminUi($original);

        if ($original === $updated) {
            $this->info('package.json already up to date, skipping');

            return;
        }

        $this->filesystem->put($packageJsonFile, $updated);
        $this->info('package.json changed');
    }
}
