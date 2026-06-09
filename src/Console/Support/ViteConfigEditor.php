<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Console\Support;

final readonly class ViteConfigEditor
{
    private const array ADMIN_INPUTS = [
        'resources/css/admin/admin.scss',
        'resources/js/admin/admin.js',
    ];

    private const array REQUIRED_IMPORTS = [
        ["import vue from '@vitejs/plugin-vue';", '@vitejs/plugin-vue'],
        ["import path from 'path';", "from 'path'"],
        ["import fs from 'fs';", "from 'fs'"],
    ];

    private const array CONFIG_KEY_ORDER = ['css', 'resolve', 'plugins', 'server'];

    public function installAdminUi(string $source, bool $isTypeScript = false): string
    {
        // Abort safely on the function-form `defineConfig(() => ({ ... }))`, which we cannot
        // merge into. Returning the source unchanged keeps the existing config intact.
        if ($this->isFunctionFormDefineConfig($source)) {
            return $source;
        }

        $imports = $this->parseImports($source);
        $config = $this->parseDefineConfigBody($source);

        $imports = $this->mergeImports($imports);
        $config = $this->mergeConfig($config);

        return $this->render($imports, $config, $isTypeScript);
    }

    private function isFunctionFormDefineConfig(string $source): bool
    {
        $marker = 'defineConfig(';
        $pos = strpos($source, $marker);
        if ($pos === false) {
            return false;
        }

        $pos += strlen($marker);
        $len = strlen($source);
        while ($pos < $len && ctype_space($source[$pos])) {
            $pos++;
        }

        // Object form starts with `{`; anything else (e.g. `(`, identifier, `async`) is a callback.
        return $pos < $len && $source[$pos] !== '{';
    }

    /**
     * @return list<string>
     */
    private function parseImports(string $source): array
    {
        preg_match_all('/^import\s+.+;\s*$/m', $source, $matches);

        return array_map(static fn (string $line) => rtrim($line), $matches[0]);
    }

    /**
     * @return array<string, string>
     */
    private function parseDefineConfigBody(string $source): array
    {
        $marker = 'defineConfig(';
        $pos = strpos($source, $marker);
        if ($pos === false) {
            return [];
        }

        $pos += strlen($marker);
        $len = strlen($source);
        while ($pos < $len && ctype_space($source[$pos])) {
            $pos++;
        }
        if ($pos >= $len || $source[$pos] !== '{') {
            return [];
        }

        $end = $this->findMatchingClose($source, $pos);
        if ($end === null) {
            return [];
        }

        return $this->parseObjectEntries(substr($source, $pos + 1, $end - $pos - 1));
    }

    /**
     * @return array<string, string>
     */
    private function parseObjectEntries(string $body): array
    {
        $result = [];
        foreach ($this->splitTopLevel($body, ',') as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            [$key, $value] = $this->parsePair($entry);
            if ($key !== '') {
                $result[$key] = trim($value);
            }
        }

        return $result;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parsePair(string $pair): array
    {
        $depth = 0;
        $inString = null;
        $len = strlen($pair);

        for ($i = 0; $i < $len; $i++) {
            $c = $pair[$i];

            if ($inString !== null) {
                if ($c === '\\' && $i + 1 < $len) {
                    $i++;

                    continue;
                }
                if ($c === $inString) {
                    $inString = null;
                }

                continue;
            }

            if ($c === "'" || $c === '"') {
                $inString = $c;

                continue;
            }
            if ($c === '{' || $c === '[' || $c === '(') {
                $depth++;

                continue;
            }
            if ($c === '}' || $c === ']' || $c === ')') {
                $depth--;

                continue;
            }
            if ($c === ':' && $depth === 0) {
                return [
                    $this->unquote(trim(substr($pair, 0, $i))),
                    substr($pair, $i + 1),
                ];
            }
        }

        return ['', $pair];
    }

    private function findMatchingClose(string $source, int $openPos): ?int
    {
        $depth = 0;
        $len = strlen($source);
        $inString = null;

        for ($i = $openPos; $i < $len; $i++) {
            $c = $source[$i];

            if ($inString !== null) {
                if ($c === '\\' && $i + 1 < $len) {
                    $i++;

                    continue;
                }
                if ($c === $inString) {
                    $inString = null;
                }

                continue;
            }

            if ($c === "'" || $c === '"') {
                $inString = $c;

                continue;
            }
            if ($c === '{' || $c === '[' || $c === '(') {
                $depth++;

                continue;
            }
            if ($c === '}' || $c === ']' || $c === ')') {
                $depth--;
                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function splitTopLevel(string $body, string $separator): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $inString = null;
        $len = strlen($body);

        for ($i = 0; $i < $len; $i++) {
            $c = $body[$i];

            if ($inString !== null) {
                $current .= $c;
                if ($c === '\\' && $i + 1 < $len) {
                    $current .= $body[$i + 1];
                    $i++;

                    continue;
                }
                if ($c === $inString) {
                    $inString = null;
                }

                continue;
            }

            if ($c === "'" || $c === '"') {
                $inString = $c;
                $current .= $c;

                continue;
            }
            if ($c === '{' || $c === '[' || $c === '(') {
                $depth++;
                $current .= $c;

                continue;
            }
            if ($c === '}' || $c === ']' || $c === ')') {
                $depth--;
                $current .= $c;

                continue;
            }
            if ($c === $separator && $depth === 0) {
                $parts[] = $current;
                $current = '';

                continue;
            }
            $current .= $c;
        }

        if (trim($current) !== '') {
            $parts[] = $current;
        }

        return $parts;
    }

    private function unquote(string $value): string
    {
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    /**
     * @param list<string> $existing
     * @return list<string>
     */
    private function mergeImports(array $existing): array
    {
        $existingText = implode("\n", $existing);

        foreach (self::REQUIRED_IMPORTS as [$line, $marker]) {
            if (!str_contains($existingText, $marker)) {
                $existing[] = $line;
            }
        }

        return array_values($existing);
    }

    /**
     * @param array<string, string> $config
     * @return array<string, string>
     */
    private function mergeConfig(array $config): array
    {
        $config = $this->mergeCss($config);
        $config = $this->mergeResolve($config);

        return $this->mergePlugins($config);
    }

    /**
     * @param array<string, string> $config
     * @return array<string, string>
     */
    private function mergeCss(array $config): array
    {
        if (!isset($config['css']) || !str_contains($config['css'], 'silenceDeprecations')) {
            $config['css'] = $this->canonicalCssBlock();
        }

        return $config;
    }

    /**
     * @param array<string, string> $config
     * @return array<string, string>
     */
    private function mergeResolve(array $config): array
    {
        if (!isset($config['resolve'])) {
            $config['resolve'] = $this->canonicalResolveBlock();

            return $config;
        }

        $hasVue = str_contains($config['resolve'], 'vue/dist/vue.esm-bundler.js');
        $hasAxios = str_contains($config['resolve'], 'axios/dist/esm/axios.js');

        if (!$hasVue || !$hasAxios) {
            $config['resolve'] = $this->canonicalResolveBlock();
        }

        return $config;
    }

    /**
     * @param array<string, string> $config
     * @return array<string, string>
     */
    private function mergePlugins(array $config): array
    {
        if (!isset($config['plugins'])) {
            return $config;
        }

        $plugins = $this->parsePluginsArray($config['plugins']);

        foreach ($plugins as $i => $plugin) {
            if ($plugin['name'] === 'laravel') {
                $plugins[$i]['raw'] = $this->ensureLaravelInputs($plugin['raw']);
            }
        }

        if (!$this->hasPlugin($plugins, 'craftableOverrides')) {
            array_unshift($plugins, ['name' => 'craftableOverrides', 'raw' => 'craftableOverrides()']);
        }

        if (!$this->hasPlugin($plugins, 'vue')) {
            $vueEntry = ['name' => 'vue', 'raw' => $this->canonicalVuePlugin()];
            $tailwindIdx = $this->findPluginIndex($plugins, 'tailwindcss');
            if ($tailwindIdx !== null) {
                array_splice($plugins, $tailwindIdx + 1, 0, [$vueEntry]);
            } else {
                $plugins[] = $vueEntry;
            }
        }

        $config['plugins'] = $this->renderPluginsArray($plugins);

        return $config;
    }

    /**
     * @return list<array{name: string, raw: string}>
     */
    private function parsePluginsArray(string $raw): array
    {
        $raw = trim($raw);
        if (!str_starts_with($raw, '[') || !str_ends_with($raw, ']')) {
            return [];
        }

        $result = [];
        foreach ($this->splitTopLevel(substr($raw, 1, -1), ',') as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            $name = '';
            if (preg_match('/^([a-zA-Z_$][a-zA-Z0-9_$]*)\s*\(/', $entry, $m)) {
                $name = $m[1];
            }

            $result[] = ['name' => $name, 'raw' => $entry];
        }

        return $result;
    }

    /**
     * @param list<array{name: string, raw: string}> $plugins
     */
    private function hasPlugin(array $plugins, string $name): bool
    {
        return $this->findPluginIndex($plugins, $name) !== null;
    }

    /**
     * @param list<array{name: string, raw: string}> $plugins
     */
    private function findPluginIndex(array $plugins, string $name): ?int
    {
        foreach ($plugins as $i => $plugin) {
            if ($plugin['name'] === $name) {
                return $i;
            }
        }

        return null;
    }

    private function ensureLaravelInputs(string $raw): string
    {
        if (!preg_match('/input\s*:\s*\[/', $raw, $m, PREG_OFFSET_CAPTURE)) {
            return $raw;
        }

        $arrayStart = $m[0][1] + strlen($m[0][0]) - 1;
        $arrayEnd = $this->findMatchingClose($raw, $arrayStart);
        if ($arrayEnd === null) {
            return $raw;
        }

        $items = $this->parseStringList(substr($raw, $arrayStart + 1, $arrayEnd - $arrayStart - 1));
        foreach (self::ADMIN_INPUTS as $path) {
            if (!in_array($path, $items, true)) {
                $items[] = $path;
            }
        }

        $rendered = "[\n";
        foreach ($items as $item) {
            $rendered .= sprintf("                '%s',\n", $item);
        }
        $rendered .= '            ]';

        return substr($raw, 0, $arrayStart) . $rendered . substr($raw, $arrayEnd + 1);
    }

    /**
     * @return list<string>
     */
    private function parseStringList(string $body): array
    {
        preg_match_all("/'([^']*)'|\"([^\"]*)\"/", $body, $matches);

        $items = [];
        foreach ($matches[0] as $i => $_) {
            $items[] = $matches[1][$i] !== '' ? $matches[1][$i] : $matches[2][$i];
        }

        return $items;
    }

    /**
     * @param list<array{name: string, raw: string}> $plugins
     */
    private function renderPluginsArray(array $plugins): string
    {
        $output = "[\n";
        foreach ($plugins as $plugin) {
            $output .= sprintf("        %s,\n", $plugin['raw']);
        }
        $output .= '    ]';

        return $output;
    }

    /**
     * @param list<string> $imports
     * @param array<string, string> $config
     */
    private function render(array $imports, array $config, bool $isTypeScript): string
    {
        $output = implode("\n", $imports) . "\n\n";
        $output .= $this->canonicalCraftableOverridesFunction($isTypeScript) . "\n\n";
        $output .= "export default defineConfig({\n";

        $remaining = $config;
        foreach (self::CONFIG_KEY_ORDER as $key) {
            if (isset($remaining[$key])) {
                $output .= sprintf("    %s: %s,\n", $key, $remaining[$key]);
                unset($remaining[$key]);
            }
        }
        foreach ($remaining as $key => $value) {
            $output .= sprintf("    %s: %s,\n", $key, $value);
        }

        return $output . "});\n";
    }

    private function canonicalCraftableOverridesFunction(bool $isTypeScript): string
    {
        $sourceParam = $isTypeScript ? 'source: string' : 'source';

        return "function craftableOverrides() {\n"
            . "    const prefix = '@craftable/';\n"
            . "    return {\n"
            . "        name: 'craftable-overrides',\n"
            . "        resolveId(" . $sourceParam . ") {\n"
            . "            if (!source.startsWith(prefix)) return null;\n"
            . "            const file = source.slice(prefix.length);\n"
            . "            const projectPath = path.resolve('resources/js/admin', file);\n"
            . "            if (fs.existsSync(projectPath)) return projectPath;\n"
            . "            const packagePath = path.resolve('node_modules/@dejwcake/craftable/src', file);\n"
            . "            if (fs.existsSync(packagePath)) return packagePath;\n"
            . "            return null;\n"
            . "        },\n"
            . "    };\n"
            . '}';
    }

    private function canonicalCssBlock(): string
    {
        return "{\n"
            . "        preprocessorOptions: {\n"
            . "            scss: {\n"
            . "                silenceDeprecations: ['import', 'global-builtin', 'color-functions'],\n"
            . "            },\n"
            . "        },\n"
            . '    }';
    }

    private function canonicalResolveBlock(): string
    {
        return "{\n"
            . "        alias: {\n"
            . "            vue: 'vue/dist/vue.esm-bundler.js',\n"
            . "            axios: path.resolve('node_modules/axios/dist/esm/axios.js'),\n"
            . "        },\n"
            . '    }';
    }

    private function canonicalVuePlugin(): string
    {
        return "vue({\n"
            . "            template: {\n"
            . "                transformAssetUrls: {\n"
            . "                    base: null,\n"
            . "                    includeAbsolute: false,\n"
            . "                },\n"
            . "            },\n"
            . '        })';
    }
}
