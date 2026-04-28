import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import fs from 'fs';

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

export default defineConfig({
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: ['import', 'global-builtin', 'color-functions'],
            },
        },
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            axios: path.resolve('node_modules/axios/dist/esm/axios.js'),
        },
    },
    plugins: [
        craftableOverrides(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin/admin.scss',
                'resources/js/admin/admin.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
