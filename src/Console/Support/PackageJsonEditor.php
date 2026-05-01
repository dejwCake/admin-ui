<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Console\Support;

final readonly class PackageJsonEditor
{
    private const array REQUIRED_DEV_DEPENDENCIES = [
        '@dejwcake/craftable' => '^2.0.0',
        '@tailwindcss/vite' => '^4.2.4',
        '@vitejs/plugin-vue' => '^6.0.0',
        'axios' => '^1.15.2',
        'laravel-vite-plugin' => '^3.0.1',
        'sass' => '^1.99.0',
        'tailwindcss' => '^4.2.4',
    ];

    private const array REQUIRED_SCRIPTS = [
        'publish:craftable-components' => 'craftable-publish-components',
    ];

    public function installAdminUi(string $packageJson): string
    {
        /** @var array<string, string|bool|array<string, string>> $content */
        $content = json_decode($packageJson, true, flags: JSON_THROW_ON_ERROR);

        $content['scripts'] = $this->mergeScripts($content['scripts'] ?? []);
        $content['devDependencies'] = $this->mergeDevDependencies($content['devDependencies'] ?? []);

        return json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }

    /**
     * @param array<string, string> $existing
     * @return array<string, string>
     */
    private function mergeScripts(array $existing): array
    {
        foreach (self::REQUIRED_SCRIPTS as $name => $command) {
            if (!isset($existing[$name])) {
                $existing[$name] = $command;
            }
        }

        return $existing;
    }

    /**
     * @param array<string, string> $existing
     * @return array<string, string>
     */
    private function mergeDevDependencies(array $existing): array
    {
        foreach (self::REQUIRED_DEV_DEPENDENCIES as $name => $requiredVersion) {
            if (!isset($existing[$name]) || $this->isLowerVersion($existing[$name], $requiredVersion)) {
                $existing[$name] = $requiredVersion;
            }
        }

        return $this->sortDependencies($existing);
    }

    private function isLowerVersion(string $existing, string $required): bool
    {
        return version_compare($this->stripRangePrefix($existing), $this->stripRangePrefix($required), '<');
    }

    private function stripRangePrefix(string $version): string
    {
        return ltrim($version, '^~>=< ');
    }

    /**
     * @param array<string, string> $deps
     * @return array<string, string>
     */
    private function sortDependencies(array $deps): array
    {
        uksort($deps, static function (string $a, string $b): int {
            $aIsScoped = str_starts_with($a, '@');
            $bIsScoped = str_starts_with($b, '@');

            if ($aIsScoped !== $bIsScoped) {
                return $aIsScoped ? -1 : 1;
            }

            return strcmp($a, $b);
        });

        return $deps;
    }
}
