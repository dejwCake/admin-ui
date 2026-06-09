<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Console\Support;

use Brackets\AdminUI\Console\Support\ViteConfigEditor;
use Override;
use PHPUnit\Framework\TestCase;

final class ViteConfigEditorTest extends TestCase
{
    private ViteConfigEditor $viteConfigEditor;

    private string $stub;

    private const string BASE_LARAVEL_VITE_CONFIG = <<<'JS'
        import { defineConfig } from 'vite';
        import laravel from 'laravel-vite-plugin';
        import tailwindcss from '@tailwindcss/vite';

        export default defineConfig({
            plugins: [
                laravel({
                    input: ['resources/css/app.css', 'resources/js/app.js'],
                    refresh: true,
                }),
                tailwindcss(),
            ],
            server: {
                watch: {
                    ignored: ['**/storage/framework/views/**'],
                },
            },
        });

        JS;

    private const string BASE_INERTIA_VITE_CONFIG = <<<'TS'
        import inertia from '@inertiajs/vite';
        import { wayfinder } from '@laravel/vite-plugin-wayfinder';
        import tailwindcss from '@tailwindcss/vite';
        import vue from '@vitejs/plugin-vue';
        import laravel from 'laravel-vite-plugin';
        import { bunny } from 'laravel-vite-plugin/fonts';
        import { defineConfig } from 'vite';

        export default defineConfig({
            plugins: [
                laravel({
                    input: ['resources/css/app.css', 'resources/js/app.ts'],
                    refresh: true,
                    fonts: [
                        bunny('Instrument Sans', {
                            weights: [400, 500, 600],
                        }),
                    ],
                }),
                inertia(),
                tailwindcss(),
                vue({
                    template: {
                        transformAssetUrls: {
                            base: null,
                            includeAbsolute: false,
                        },
                    },
                }),
                wayfinder({
                    formVariants: true,
                }),
            ],
        });

        TS;

    private const string FUNCTION_FORM_VITE_CONFIG = <<<'TS'
        import { defineConfig } from 'vite';
        import laravel from 'laravel-vite-plugin';

        export default defineConfig(() => ({
            plugins: [
                laravel({
                    input: ['resources/css/app.css', 'resources/js/app.ts'],
                    refresh: true,
                }),
            ],
        }));

        TS;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->viteConfigEditor = new ViteConfigEditor();
        $this->stub = (string) file_get_contents(__DIR__ . '/../../../../install-stubs/vite.config.js');
    }

    public function testInstallOnBaseLaravelViteConfigProducesStubContent(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);

        self::assertSame($this->stub, $result);
    }

    public function testInstallOnAlreadyInstalledViteConfigIsIdempotent(): void
    {
        $result = $this->viteConfigEditor->installAdminUi($this->stub);

        self::assertSame($this->stub, $result);
    }

    public function testInstallOnBaseConfigPreservesServerSection(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);

        self::assertStringContainsString(
            "server: {\n        watch: {\n            ignored: ['**/storage/framework/views/**'],\n        },\n    },",
            $result,
        );
    }

    public function testInstallAddsCraftableOverridesAsFirstPlugin(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);

        self::assertStringContainsString("plugins: [\n        craftableOverrides(),\n        laravel(", $result);
    }

    public function testInstallAddsAdminInputEntriesToLaravelPlugin(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);

        self::assertStringContainsString("'resources/css/admin/admin.scss',", $result);
        self::assertStringContainsString("'resources/js/admin/admin.js',", $result);
    }

    public function testInstallAddsRequiredImports(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);

        self::assertStringContainsString("import vue from '@vitejs/plugin-vue';", $result);
        self::assertStringContainsString("import path from 'path';", $result);
        self::assertStringContainsString("import fs from 'fs';", $result);
    }

    public function testInstallDoesNotDuplicateImportsOnRepeatedRun(): void
    {
        $first = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);
        $second = $this->viteConfigEditor->installAdminUi($first);

        self::assertSame($first, $second);
        self::assertSame(1, substr_count($second, "import vue from '@vitejs/plugin-vue';"));
        self::assertSame(1, substr_count($second, "import path from 'path';"));
        self::assertSame(1, substr_count($second, "import fs from 'fs';"));
    }

    public function testInstallOnInertiaConfigAddsCraftableOverridesAsFirstPlugin(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);

        self::assertStringContainsString("plugins: [\n        craftableOverrides(),\n        laravel(", $result);
    }

    public function testInstallOnInertiaConfigPreservesStarterKitPlugins(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);

        self::assertStringContainsString('inertia()', $result);
        self::assertStringContainsString('wayfinder({', $result);
        self::assertStringContainsString("bunny('Instrument Sans'", $result);
        // The existing vue() plugin is kept; a second one is not appended.
        self::assertSame(1, substr_count($result, 'vue({'));
    }

    public function testInstallOnInertiaConfigPreservesTypeScriptEntryAndAddsAdminInputs(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);

        self::assertStringContainsString("'resources/js/app.ts',", $result);
        self::assertStringContainsString("'resources/css/admin/admin.scss',", $result);
        self::assertStringContainsString("'resources/js/admin/admin.js',", $result);
    }

    public function testInstallOnInertiaConfigInjectsResolveAndCss(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);

        self::assertStringContainsString("vue: 'vue/dist/vue.esm-bundler.js',", $result);
        self::assertStringContainsString("axios: path.resolve('node_modules/axios/dist/esm/axios.js'),", $result);
        self::assertStringContainsString(
            "silenceDeprecations: ['import', 'global-builtin', 'color-functions'],",
            $result,
        );
    }

    public function testInstallOnTypeScriptConfigEmitsTypedResolveId(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);

        self::assertStringContainsString('resolveId(source: string) {', $result);
        self::assertStringNotContainsString('resolveId(source) {', $result);
    }

    public function testInstallOnJavaScriptConfigEmitsUntypedResolveId(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);

        self::assertStringContainsString('resolveId(source) {', $result);
        self::assertStringNotContainsString('resolveId(source: string) {', $result);
    }

    public function testInstallOnInertiaConfigIsIdempotent(): void
    {
        $first = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);
        $second = $this->viteConfigEditor->installAdminUi($first, isTypeScript: true);

        self::assertSame($first, $second);
    }

    public function testInstallDisablesWayfinderPhpCommand(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);

        // Wayfinder no longer shells out to php on build; existing options are preserved.
        self::assertStringContainsString("wayfinder({ formVariants: true, command: 'true' })", $result);
    }

    public function testInstallDisablingWayfinderCommandIsIdempotent(): void
    {
        $first = $this->viteConfigEditor->installAdminUi(self::BASE_INERTIA_VITE_CONFIG, isTypeScript: true);
        $second = $this->viteConfigEditor->installAdminUi($first, isTypeScript: true);

        self::assertSame($first, $second);
        self::assertSame(1, substr_count($second, "command: 'true'"));
    }

    public function testInstallWithoutWayfinderDoesNotAddCommand(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::BASE_LARAVEL_VITE_CONFIG);

        self::assertStringNotContainsString("command: 'true'", $result);
    }

    public function testInstallLeavesFunctionFormConfigUnchanged(): void
    {
        $result = $this->viteConfigEditor->installAdminUi(self::FUNCTION_FORM_VITE_CONFIG, isTypeScript: true);

        self::assertSame(self::FUNCTION_FORM_VITE_CONFIG, $result);
    }
}
