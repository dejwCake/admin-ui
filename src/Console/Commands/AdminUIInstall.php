<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Console\Commands;

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
        $viteConfigPath = $this->app->basePath('vite.config.js');

        if (!$this->filesystem->exists($viteConfigPath)) {
            $this->filesystem->copy(__DIR__ . '/../../../install-stubs/vite.config.js', $viteConfigPath);
            $this->info('Vite configuration created');

            return;
        }

        $original = $this->filesystem->get($viteConfigPath);
        $updated = $this->viteConfigEditor->installAdminUi($original);

        if ($original === $updated) {
            $this->info('Vite configuration already up to date, skipping');

            return;
        }

        $this->filesystem->put($viteConfigPath, $updated);
        $this->info('Vite configuration updated');
    }

    /**
     * @throws FileNotFoundException
     */
    private function adjustPackageJson(): void
    {
        $this->info('Changing package.json');
        $packageJsonFile = $this->app->basePath('package.json');
        $packageJson = $this->filesystem->get($packageJsonFile);
        $packageJsonContent = json_decode($packageJson, true);

        $packageJsonContent['scripts']['publish:craftable-components'] = 'craftable-publish-components';

        $packageJsonContent['devDependencies']['@dejwcake/craftable'] = '^2.0.0';
        $packageJsonContent['devDependencies']['@vitejs/plugin-vue'] = '^6.0.0';
        $packageJsonContent['devDependencies']['sass'] = '^1.32.6';

        $this->filesystem->put($packageJsonFile, json_encode($packageJsonContent, JSON_PRETTY_PRINT));
        $this->info('package.json changed');
    }
}
