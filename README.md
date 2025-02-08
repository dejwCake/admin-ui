# Admin UI

Admin UI is an administration template for Laravel 11. It provides admin layout and basic UI elements to build up an administration area (CMS, e-shop, back-office, ...).

Example of an administration interface built with this package:
![Craftable administration area example](https://docs.getcraftable.com/assets/posts-crud.png "Craftable administration area example")

This packages is part of [Craftable](https://github.com/BRACKETS-by-TRIAD/craftable) (`brackets/craftable`) - an administration starter kit for Laravel 11. You should definitely have a look :)

You can find full documentation at https://docs.getcraftable.com/#/admin-ui

## Composer

To develop this package, you need to have composer installed. To run composer command use:

```shell
  docker compose run -it --rm test composer update
```

## Run tests

To run tests use this docker environment.

```shell
  docker compose run -it --rm test vendor/bin/phpunit  -d pcov.enabled=1
```

To switch between postgresql and mariadb change in `docker-compose.yml` DB_CONNECTION environmental variable:

```git
- DB_CONNECTION: pgsql
+ DB_CONNECTION: mysql
```

## Run code analysis tools

To be sure, that your code is clean, you can run code analysis tools. To do this, run:

For composer normalization:
```shell
  docker compose run -it --rm php-qa composer normalize
```

For php compatibility:
```shell
  docker compose run -it --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
```

For code style:
```shell
  docker compose run -it --rm php-qa phpcs -s --colors --extensions=php
```

or to fix issues:

```shell
  docker compose run -it --rm php-qa phpcbf -s --colors --extensions=php
```

For static analysis:
```shell
  docker compose run -it --rm php-qa phpstan analyse --configuration=phpstan.neon
```

For mess detector:
```shell
  docker compose run -it --rm php-qa phpmd ./src,./routes,./resources,./install-stubs,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
```

## Issues
Where do I report issues?
If something is not working as expected, please open an issue in the main repository https://github.com/BRACKETS-by-TRIAD/craftable.
