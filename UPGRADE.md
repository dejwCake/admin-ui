# Upgrade Guide: v1 to v2

## Requirements

- PHP ^8.5 (was ^8.2)
- Laravel 13 (was 12)
- Vite (replaces Webpack/Laravel Mix)
- Vue 3 Composition API (replaces Vue 2 Options API)
- Bootstrap 5 / CoreUI 5 (replaces Bootstrap 4 / CoreUI 2)

## Dependency Changes

### composer.json

```diff
 "require": {
-    "php": "^8.2",
+    "php": "^8.5",
 },
 "require-dev": {
-    "larastan/larastan": "^3.1",
-    "orchestra/testbench-browser-kit": "^10.0",
-    "phpunit/phpunit": "^11.5.9"
+    "larastan/larastan": "^3.9",
+    "orchestra/testbench": "^11.0",
+    "phpunit/phpunit": "^13.0"
 },
+"minimum-stability": "dev",
+"prefer-stable": true
```

`orchestra/testbench-browser-kit` has been replaced with `orchestra/testbench`.

## Build System: Webpack to Vite

### Removed files

Delete from your project:
- `webpack.mix.js`
- `install-stubs/webpack.mix.js`
- `install-stubs/partial-webpack.mix.js`

### New Vite configuration

The install command (`admin-ui:install`) now modifies `vite.config.js` programmatically instead of publishing a webpack config. It:
- Adds `@vitejs/plugin-vue` import and plugin
- Adds `craftableOverrides()` function for `@craftable/` import prefix resolution
- Adds admin entry points (`resources/css/admin/admin.scss`, `resources/js/admin/admin.js`)
- Adds Vue ESM bundler alias
- Adds SCSS deprecation silencing

If no `vite.config.js` exists, a complete stub is published from `install-stubs/vite.config.js`.

### package.json

The install command now adds Vite-compatible dependencies:
- `@dejwcake/craftable`
- `@vitejs/plugin-vue`
- `sass`

The `"type": "module"` field is now preserved (Vite requires it — opposite of Webpack).

## Frontend: Vue 2 to Vue 3

### admin.js

Replace Vue 2 `new Vue()` with Vue 3 `createApp()`:

```diff
-import Vue from 'vue'
-window.Vue = Vue
-new Vue({ el: '#app' })
+import { createApp } from 'vue'
+import { useAdmin } from '@craftable/composables/useAdmin.js'
+import { initUI } from '@craftable/ui/index.js'
+import { initDateFnsLocale } from '@craftable/utils/dateFnsLocale.js'
+const app = createApp({
+    setup() { return useAdmin() }
+})
+// register components...
+initDateFnsLocale().then(() => { app.mount('#app'); initUI() })
```

### Auto-generation markers in `admin.js`

The single anchor markers used by `admin-generator`'s `registerVueComponent` (`//-- Do not delete me :) I'm used for auto-generation js import --` and `//-- Do not delete me :) I'm used for auto-generation component registration --`) have been replaced with **begin/end pairs**. The install-stub now ships:

```js
//-- Do not delete me :) I'm used for auto-generation js import begin --
//-- Do not delete me :) I'm used for auto-generation js import end --
```

```js
//-- Do not delete me :) I'm used for auto-generation component registration begin --
//-- Do not delete me :) I'm used for auto-generation component registration end --
```

Auto-generated `import` and `app.component(...)` lines live between these markers and are re-sorted alphabetically by component name on each generation. See the corresponding section in `admin-generator/UPGRADE.md` for the manual migration steps.

### bootstrap.js

- Removed: jQuery, lodash, moment.js, `window.Vue` global
- Kept: axios with CSRF token setup
- Added: vee-validate 4 configuration (`defineRule`, `configure`, `localize`)

### Mixins replaced by Composables

The Vue 2 mixin pattern (`app-components/`) has been replaced with Vue 3 composables:

| v1 (Deleted) | v2 (New) |
|---|---|
| `app-components/Form/AppForm.js` | `composables/useAppForm.js` |
| `app-components/Form/AppUpload.js` | _(removed, handled by `@craftable/`)_ |
| `app-components/Listing/AppListing.js` | `composables/useAppListing.js` |
| `app-components/bootstrap.js` | _(removed)_ |
| `index.js` | _(removed)_ |

The composables wrap `@craftable/` base composables and serve as project-level extension points:

```js
// composables/useAppForm.js
import { useBaseForm } from '@craftable/composables/useBaseForm.js';
export function useAppForm(props, options = {}) {
    const baseForm = useBaseForm(props, options);
    return { ...baseForm };
}
```

## SCSS Path Change

SCSS files moved from `resources/sass/admin/` to `resources/css/admin/` (Vite convention):

```diff
-resources/sass/admin/_variables.scss
-resources/sass/admin/admin.scss
-resources/sass/admin/styles/_index.scss
-resources/sass/admin/vendor/_index.scss
+resources/css/admin/_variables.scss
+resources/css/admin/admin.scss
+resources/css/admin/styles/_index.scss
+resources/css/admin/vendor/_index.scss
```

File contents are unchanged.

## Blade Templates

### Bootstrap 4 to Bootstrap 5 class changes

In `sidebar.blade.php` and `profile-dropdown.blade.php`:

| BS4 Class | BS5 Class |
|---|---|
| `dropdown-menu-right` | `dropdown-menu-end` |
| `c-sidebar` | `sidebar` |
| `c-sidebar-brand` | `sidebar-brand` |
| `c-sidebar-nav` | `sidebar-nav` |
| `c-sidebar-nav-item` | `nav-item` |
| `c-sidebar-nav-link` | `nav-link` |
| `c-sidebar-nav-icon` | `nav-icon` |
| `c-sidebar-minimizer` | _(removed)_ |

### Views removed

- `resources/views/admin/includes/avatar-uploader.blade.php` — removed
- `resources/views/admin/includes/media-uploader.blade.php` — removed
- `resources/views/admin/partials/wysiwyg-svgs.blade.php` — removed

### Master layout changes

The master layout now uses View Composer variables instead of direct helper/facade calls:

```diff
-<html lang="{{ config('app.locale') }}">
+<html lang="{{ $appLocale }}">

-<meta name="csrf-token" content="{{ csrf_token() }}">
+<meta name="csrf-token" content="{{ $csrfToken }}">
```

### Header partial changes

The header partial now uses View Composer variables instead of `Auth` facade:

```diff
-@if(Auth::guard(config('admin-auth.defaults.guard'))->check())
-    {{ Auth::guard(config('admin-auth.defaults.guard'))->user()->full_name }}
+@if($adminUser)
+    {{ $adminUserFullName }}
```

## PHP Modernization

### Classes made `final`

- `AdminUIServiceProvider`
- `WysiwygMedia`
- `ViewComposerProvider`

### Classes made `final readonly`

- `BooleanValue`
- `StringToArray`
- `AdminLayoutComposer`
- `AdminHeaderComposer`
- `WysiwygUploadUrlComposer`

### Dependency Injection

Facades and helper functions replaced with constructor DI:

| v1 | v2 |
|---|---|
| `Image::read()` facade | `ImageManager $imageManager` injected |
| `url()` helper | `UrlGenerator $urlGenerator` injected |
| `config()` in views | View Composers with `Config` contract |
| `csrf_token()` in views | View Composer with optional `Session` |
| `Auth::` facade in views | View Composer with `AuthManager` |
| `base_path()` helper | `$this->app->basePath()` |
| `app()` helper | `$this->app->make()` |

**Exception**: `trans()` helper is still used (allowed project convention).

### New View Composers

Three new View Composers replace direct facade/helper calls in Blade templates:

- `AdminLayoutComposer` — registered for `admin.layout.master`, provides `$appLocale`, `$csrfToken`
- `AdminHeaderComposer` — registered for `admin.partials.header`, provides `$adminUser`, `$adminUserFullName`, `$adminUserInitials`, `$adminUserAvatarUrl`
- `WysiwygUploadUrlComposer` — registered for all views, provides `$wysiwygUploadUrl`

### Config and migrations moved

- `install-stubs/config/wysiwyg-media.php` moved to `config/wysiwyg-media.php` (package-level config, merged in service provider)
- `install-stubs/database/migrations/` moved to `database/migrations/` (published with full datetime timestamp)

## Test Suite

The test suite has been rewritten from scratch:

- **Framework**: PHPUnit 13 (was 11), Orchestra Testbench (was testbench-browser-kit)
- **Structure**: Split into Unit and Feature test suites
- **53 tests** covering sanitizers, traits, models, view composers, and upload controller
- Test model `TestWysiwygableModel` extracted to `tests/TestWysiwygableModel.php`
- Upload tests use temp directories and clean up after themselves

## CI Pipeline

GitHub Actions workflow updated:
- `dejwcake/phpqa8.4:2` → `dejwcake/phpqa8.5:1`
- `dejwcake/php8.2:1` → `dejwcake/php8.5:1`
- `dejwcake/postgres17:1` → `dejwcake/postgres18:1`
- `dejwcake/mariadb11.6:1` → `dejwcake/mariadb12.1:1`
