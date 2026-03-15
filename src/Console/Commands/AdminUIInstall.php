<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
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

    public function __construct(private readonly Filesystem $filesystem, private readonly Application $app,)
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
            $this->filesystem->copy(
                __DIR__ . '/../../../install-stubs/vite.config.js',
                $viteConfigPath,
            );
            $this->info('Vite configuration created');

            return;
        }

        $config = $this->filesystem->get($viteConfigPath);

        if (str_contains($config, 'resources/js/admin')) {
            $this->info('Vite configuration already contains admin entries, skipping');

            return;
        }

        $config = $this->addImports($config);
        $config = $this->addCraftableOverridesFunction($config);
        $config = $this->addAdminInputEntries($config);
        $config = $this->addPlugins($config);
        $config = $this->addConfigSections($config);

        $this->filesystem->put($viteConfigPath, $config);
        $this->info('Vite configuration updated');
    }

    private function addImports(string $config): string
    {
        $imports = [
            'vue' => "import vue from '@vitejs/plugin-vue';",
            'path' => "import path from 'path';",
            'fs' => "import fs from 'fs';",
        ];

        foreach ($imports as $module => $importLine) {
            $pattern = sprintf('/^import\s+\S+\s+from\s+[\'"]%s[\'"];?\s*$/m', preg_quote($module, '/'));
            if (!preg_match($pattern, $config)) {
                // Add after the last import line
                $config = preg_replace(
                    '/(import\s+.*from\s+[\'"][^\'"]+[\'"];?\s*\n)(?!import)/s',
                    '$1' . $importLine . "\n",
                    $config,
                    1,
                );
            }
        }

        return $config;
    }

    private function addCraftableOverridesFunction(string $config): string
    {
        if (str_contains($config, 'craftableOverrides')) {
            return $config;
        }

        $function = <<<'JS'

        function craftableOverrides() {
            const prefix = '@craftable/';
            return {
                name: 'craftable-overrides',
                resolveId(source) {
                    if (!source.startsWith(prefix)) return null;
                    const file = source.slice(prefix.length);
                    const projectPath = path.resolve('resources/js/admin', file);
                    if (fs.existsSync(projectPath)) return projectPath;
                    const packagePath = path.resolve('node_modules/@dejwcake/craftable/src', file);
                    if (fs.existsSync(packagePath)) return packagePath;
                    return null;
                },
            };
        }

        JS;

        return preg_replace(
            '/(\n*)(export\s+default)/',
            $function . "\n$2",
            $config,
            1,
        );
    }

    private function addAdminInputEntries(string $config): string
    {
        if (!preg_match('/input\s*:\s*(\[.*?\])/s', $config, $matches)) {
            return $config;
        }

        $inputJson = $matches[1];
        // JS array of strings is valid JSON
        $input = json_decode($inputJson, true);
        if (!is_array($input)) {
            return $config;
        }

        $input[] = 'resources/css/admin/admin.scss';
        $input[] = 'resources/js/admin/admin.js';

        // Format as indented JS array
        $lines = array_map(static fn (string $entry) => "            '$entry',", $input);
        $newInput = "[\n" . implode("\n", $lines) . "\n        ]";

        return str_replace($inputJson, $newInput, $config);
    }

    private function addPlugins(string $config): string
    {
        // Add craftableOverrides() before laravel()
        if (!str_contains($config, 'craftableOverrides()')) {
            $config = preg_replace(
                '/(plugins\s*:\s*\[\s*\n)(\s*)(laravel\s*\()/',
                "$1$2craftableOverrides(),\n$2$3",
                $config,
                1,
            );
        }

        // Add vue() plugin after tailwindcss()
        if (!preg_match('/vue\s*\(/', $config)) {
            $vuePlugin = <<<'JS'
                    vue({
                        template: {
                            transformAssetUrls: {
                                base: null,
                                includeAbsolute: false,
                            },
                        },
                    }),
            JS;

            $config = preg_replace(
                '/(tailwindcss\s*\(\s*\)\s*,?\s*\n)/',
                "$1" . $vuePlugin . "\n",
                $config,
                1,
            );
        }

        return $config;
    }

    private function addConfigSections(string $config): string
    {
        // Add SCSS silenceDeprecations
        if (!str_contains($config, 'silenceDeprecations')) {
            $css = [
                'preprocessorOptions' => [
                    'scss' => [
                        'silenceDeprecations' => ['import', 'global-builtin', 'color-functions'],
                    ],
                ],
            ];
            $cssJs = $this->phpArrayToJsObject($css, 2);

            $config = preg_replace(
                '/(export\s+default\s+defineConfig\s*\(\s*\{\s*\n)/',
                "$1    css: $cssJs,\n",
                $config,
                1,
            );
        }

        // Add vue alias
        if (!str_contains($config, 'vue/dist/vue.esm-bundler.js')) {
            if (preg_match('/resolve\s*:\s*\{/', $config)) {
                $config = preg_replace(
                    '/(resolve\s*:\s*\{\s*\n\s*alias\s*:\s*\{)/',
                    "$1\n            vue: 'vue/dist/vue.esm-bundler.js',",
                    $config,
                    1,
                );
            } else {
                $resolve = ['alias' => ['vue' => 'vue/dist/vue.esm-bundler.js']];
                $resolveJs = $this->phpArrayToJsObject($resolve, 2);

                $config = preg_replace(
                    '/(export\s+default\s+defineConfig\s*\(\s*\{\s*\n)/',
                    "$1    resolve: $resolveJs,\n",
                    $config,
                    1,
                );
            }
        }

        return $config;
    }

    /**
     * Convert a PHP array to a JS object literal string.
     */
    private function phpArrayToJsObject(mixed $data, int $indent = 1): string
    {
        $spaces = str_repeat('    ', $indent);
        $innerSpaces = str_repeat('    ', $indent + 1);

        if (is_array($data) && array_is_list($data)) {
            $items = array_map(fn (mixed $item) => $innerSpaces . $this->phpValueToJs($item), $data);

            return "[\n" . implode(",\n", $items) . ",\n$spaces]";
        }

        $lines = [];
        foreach ($data as $key => $value) {
            $jsValue = is_array($value) ? $this->phpArrayToJsObject($value, $indent + 1) : $this->phpValueToJs($value);
            $lines[] = "$innerSpaces$key: $jsValue";
        }

        return "{\n" . implode(",\n", $lines) . ",\n$spaces}";
    }

    private function phpValueToJs(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . addcslashes($value, "'") . "'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
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

        $packageJsonContent['devDependencies']['@dejwcake/craftable'] = '^0.0.3';
        $packageJsonContent['devDependencies']['@vitejs/plugin-vue'] = '^5.2.3';
        $packageJsonContent['devDependencies']['sass'] = '^1.32.6';

        $this->filesystem->put($packageJsonFile, json_encode($packageJsonContent, JSON_PRETTY_PRINT));
        $this->info('package.json changed');
    }
}
