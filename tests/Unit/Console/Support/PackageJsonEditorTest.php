<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Console\Support;

use Brackets\AdminUI\Console\Support\PackageJsonEditor;
use Override;
use PHPUnit\Framework\TestCase;

final class PackageJsonEditorTest extends TestCase
{
    private PackageJsonEditor $packageJsonEditor;

    private const string BASE_PACKAGE_JSON = <<<'JSON'
        {
            "$schema": "https://www.schemastore.org/package.json",
            "private": true,
            "type": "module",
            "scripts": {
                "build": "vite build",
                "dev": "vite"
            },
            "devDependencies": {
                "@tailwindcss/vite": "^4.0.0",
                "concurrently": "^9.0.1",
                "laravel-vite-plugin": "^3.0.0",
                "tailwindcss": "^4.0.0",
                "vite": "^8.0.0"
            }
        }
        JSON;

    private const string EXPECTED_PACKAGE_JSON = <<<'JSON'
        {
            "$schema": "https://www.schemastore.org/package.json",
            "private": true,
            "type": "module",
            "scripts": {
                "build": "vite build",
                "dev": "vite",
                "publish:craftable-components": "craftable-publish-components"
            },
            "devDependencies": {
                "@dejwcake/craftable": "^2.0.0",
                "@tailwindcss/vite": "^4.2.4",
                "@vitejs/plugin-vue": "^6.0.0",
                "axios": "^1.15.2",
                "concurrently": "^9.0.1",
                "laravel-vite-plugin": "^3.0.1",
                "sass": "^1.99.0",
                "tailwindcss": "^4.2.4",
                "vite": "^8.0.0"
            }
        }

        JSON;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->packageJsonEditor = new PackageJsonEditor();
    }

    public function testInstallOnBasePackageJsonProducesExpectedContent(): void
    {
        $result = $this->packageJsonEditor->installAdminUi(self::BASE_PACKAGE_JSON);

        self::assertSame(self::EXPECTED_PACKAGE_JSON, $result);
    }

    public function testInstallIsIdempotent(): void
    {
        $first = $this->packageJsonEditor->installAdminUi(self::BASE_PACKAGE_JSON);
        $second = $this->packageJsonEditor->installAdminUi($first);

        self::assertSame($first, $second);
    }

    public function testHigherExistingVersionIsPreserved(): void
    {
        $input = json_encode([
            'devDependencies' => [
                'tailwindcss' => '^5.0.0',
                'sass' => '^2.0.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $result = $this->packageJsonEditor->installAdminUi($input);
        $decoded = json_decode($result, true);

        self::assertSame('^5.0.0', $decoded['devDependencies']['tailwindcss']);
        self::assertSame('^2.0.0', $decoded['devDependencies']['sass']);
    }

    public function testLowerExistingVersionIsBumped(): void
    {
        $input = json_encode([
            'devDependencies' => [
                'tailwindcss' => '^4.0.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $result = $this->packageJsonEditor->installAdminUi($input);
        $decoded = json_decode($result, true);

        self::assertSame('^4.2.4', $decoded['devDependencies']['tailwindcss']);
    }

    public function testDevDependenciesAreSortedScopedFirstThenAlphabetical(): void
    {
        $result = $this->packageJsonEditor->installAdminUi(self::BASE_PACKAGE_JSON);
        $decoded = json_decode($result, true);

        self::assertSame(
            [
                '@dejwcake/craftable',
                '@tailwindcss/vite',
                '@vitejs/plugin-vue',
                'axios',
                'concurrently',
                'laravel-vite-plugin',
                'sass',
                'tailwindcss',
                'vite',
            ],
            array_keys($decoded['devDependencies']),
        );
    }

    public function testForwardSlashesAreNotEscaped(): void
    {
        $result = $this->packageJsonEditor->installAdminUi(self::BASE_PACKAGE_JSON);

        self::assertStringNotContainsString('\/', $result);
        self::assertStringContainsString('https://www.schemastore.org/package.json', $result);
    }
}
