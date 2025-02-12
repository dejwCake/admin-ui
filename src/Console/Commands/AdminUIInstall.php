<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class AdminUIInstall extends Command
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

    public function __construct(private readonly Filesystem $filesystem)
    {
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

    private function appendIfNotExists(string $filePath, string $append, ?string $ifRegexNotExists = null): bool|int
    {
        $content = $this->filesystem->get($filePath);
        if ($ifRegexNotExists !== null && preg_match($ifRegexNotExists, $content)) {
            return false;
        }

        return $this->filesystem->put($filePath, $content . $append);
    }

    /**
     * @throws FileNotFoundException
     */
    private function frontendAdjustments(): void
    {
        // webpack
        if (
            $this->filesystem->exists(base_path('webpack.mix.js'))
            && $this->appendIfNotExists(
                'webpack.mix.js',
                "\n\n" . $this->filesystem->get(__DIR__ . '/../../../install-stubs/partial-webpack.mix.js'),
                '|resources/js/admin|',
            )
        ) {
            $this->info('Webpack configuration updated');
        }

        //Change package.json
        $this->info('Changing package.json');
        $packageJsonFile = base_path('package.json');
        $packageJson = $this->filesystem->get($packageJsonFile);
        $packageJsonContent = json_decode($packageJson, true);

        if (!File::exists('webpack.mix.js')) {
            $packageJsonContent['scripts']['craftable-dev'] = 'mix';
            $packageJsonContent['scripts']['craftable-watch'] = 'mix watch';
            $packageJsonContent['scripts']['craftable-prod'] = 'mix --production';
            $packageJsonContent['devDependencies']['laravel-mix'] = '^6.0.6';
        }

        $packageJsonContent['devDependencies']['craftable'] = '^2.1.3';
        $packageJsonContent['devDependencies']['vue-loader'] = '^15.9.8';
        $packageJsonContent['devDependencies']['sass-loader'] = '^8.0.2';
        $packageJsonContent['devDependencies']['resolve-url-loader'] = '^3.1.0';
        $packageJsonContent['devDependencies']['sass'] = '^1.32.6';

        $this->filesystem->put($packageJsonFile, json_encode($packageJsonContent, JSON_PRETTY_PRINT));
        $this->info('package.json changed');
    }
}
