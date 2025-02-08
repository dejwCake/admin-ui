# Admin UI

Admin UI is an administration template for Laravel 11. It provides admin layout and basic UI elements to build up an administration area (CMS, e-shop, back-office, ...).

Example of an administration interface built with this package:
![Craftable administration area example](https://docs.getcraftable.com/assets/posts-crud.png "Craftable administration area example")

This packages is part of [Craftable](https://github.com/BRACKETS-by-TRIAD/craftable) (`brackets/craftable`) - an administration starter kit for Laravel 11. You should definitely have a look :)

You can find full documentation at https://docs.getcraftable.com/#/admin-ui

## Run tests

To run tests use this docker environment.

```shell
  docker compose run -it --rm test vendor/bin/phpunit
```

To switch between postgresql and mariadb change in `docker-compose.yml` DB_CONNECTION environmental variable:

```git
- DB_CONNECTION: pgsql
+ DB_CONNECTION: mysql
```

## Issues
Where do I report issues?
If something is not working as expected, please open an issue in the main repository https://github.com/BRACKETS-by-TRIAD/craftable.
