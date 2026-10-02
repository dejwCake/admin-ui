# AGENTS.md — dejwcake/admin-ui

Admin layout, Blade views, WYSIWYG media upload and the install stubs that wire a Laravel
project's Vite/Vue admin frontend. Composer `dejwcake/admin-ui`, namespace `Brackets\AdminUI`
(fork of `brackets/admin-ui`). Part of the Craftable ecosystem — see README.md.

## Layout

- `src/Console/` — `admin-ui:install`: patches the consumer's `vite.config.js` and `package.json`
  (`@dejwcake/craftable`, Vue plugin, `@craftable/` alias) and publishes assets.
- `install-stubs/` — files copied into consumer projects (Vite config, `resources/js/admin/`,
  SCSS, sidebar/logo/profile Blade partials, mail theme).
- `src/ViewComposers/` — layout/header/WYSIWYG-URL data for the admin views.
- `src/Http/Controllers/WysiwygMediaUploadController.php`, `src/Models/WysiwygMedia.php`,
  `src/Traits/HasWysiwygMediaTrait.php` — WYSIWYG image upload and attachment.
- `src/Http/Requests/Sanitizers/`, `src/Traits/Validated.php`, `SanitizeBoolean`.

## Commands

Everything runs in Docker from the package root — never against a host PHP. The full,
copy-pasteable list (composer, every QA tool, both databases and
the "whole PHP suite" one-liner) is in **README.md → "How to develop this project"**.
The ones you need most:

```shell
docker compose run --rm test composer update
docker compose run --rm test ./vendor/bin/phpunit                         # MariaDB (default)
docker compose run --rm -e DB_CONNECTION=pgsql test ./vendor/bin/phpunit   # PostgreSQL
docker compose run --rm php-qa phpcs -s --colors --extensions=php
docker compose run --rm php-qa phpcbf -s --colors --extensions=php       # auto-fix style
docker compose run --rm php-qa phpstan analyse --configuration=phpstan.neon
docker compose run --rm php-qa phpmd ./config,./database,./install-stubs,./lang,./resources,./routes,./src,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
docker compose run --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
docker compose run --rm php-qa composer normalize
```

A change is done when phpcs, phpstan, phpmd and the test suite are green.

## Code conventions

- PHP `^8.5`, Laravel 13. Every file starts with `declare(strict_types=1);`.
- **No Facades** — inject contracts through the constructor.
- **No helpers**, with these exceptions: `trans()` / `__()` are allowed everywhere; `app()` only in
  models, traits and places where DI is genuinely hard to provide.
- Constructor property promotion. `final` classes and `readonly` wherever possible — prefer a
  `final readonly class`, otherwise readonly properties. A readonly property is public rather than
  hidden behind a getter.
- Always import with `use`; never inline `\Fully\Qualified\Names`.
- Alias the colliding `Repository` contracts:
  `use Illuminate\Contracts\Config\Repository as Config;`,
  `use Illuminate\Contracts\Cache\Repository as Cache;`.
- Name a property after its type: `TranslationImportService $translationImportService`, not `$service`.
- Build strings with `sprintf()` — no `"{$var}"` interpolation and no `.` concatenation.
- Mark overrides with `#[Override]` — **except** a method that overrides a *trait* method
  (e.g. `HasFactory::newFactory()`): PHP 8.5.3 segfaults on that.
- Before adding a native type to an overriding property/parameter, check the parent. If the parent
  is untyped (Laravel's `$fillable`, `$hidden`, a command's `$description`, …) the child must stay
  untyped too.
- Fix new phpstan/phpmd findings in code. Baselines are for accepted, existing debt only — inspect
  the baseline diff before committing it.

## Testing conventions

- PHPUnit 13 + Orchestra Testbench 11. Test namespaces mirror `src/`.
- Several tested methods of one class → a directory named after the class with one
  `<Method>Test.php` per method.
- Feature tests when several real classes collaborate; Unit tests for isolated logic (mock the
  rest). Don't write tests for service providers or install commands.
- PHPUnit assertions are static: `self::assert*()`. Laravel's instance assertions
  (`$this->assertDatabaseHas()`, response asserts) stay on `$this`.
- Resolve services with `$this->app->make()`, never `app()`.
- Test-only models and stubs live in the `tests/` root.

## Package notes

- `install-stubs/` is consumer code: it must follow the same conventions and stay in sync with
  `@dejwcake/craftable` (the `craftable-frontend` package) — composable names, the `@craftable/`
  alias and the CoreUI 5 / Bootstrap 5 markup.
- Tests default to SQLite but branch on `DB_CONNECTION`, and run the shipped migration through
  `defineDatabaseMigrations()`.

## Versioning

The package is on **2.x** and stays there through the Laravel 13 / PHP 8.5 upgrade — don't add
v3 upgrade sections or bump the `branch-alias`. User-facing changes go to `UPGRADE.md` when
consumers have to act.
