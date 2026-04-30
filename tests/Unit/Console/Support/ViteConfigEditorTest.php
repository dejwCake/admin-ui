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
}
