# Frequently Asked Questions

This guide will help you find the answers to the most common questions about Many Notes.

## How to debug the application

### Application logs

Application errors are written to `storage/logs/laravel.log` inside the container. They are not shown in the container log, which only records web requests.

To watch for errors while you reproduce a problem, run:

```shell
docker compose exec app tail -f storage/logs/laravel.log
```

To see the last few errors instead:

```shell
docker compose exec app grep -a "\.ERROR" storage/logs/laravel.log | tail -5
```

### Debug mode

Debug mode shows the full error in the browser when a page crashes. It has no effect on errors the application handles itself, such as a failed login, which are only written to the log.

You can enable debug mode in your `compose.yaml` file by adding:

```yaml
environment:
  - APP_DEBUG=true
```

Turn it off again afterwards, because the error page exposes details about your installation.

## The search feature is not returning any results

First, make sure to mount the Typesense directory to `/var/www/html/typesense`, like is described in the [installation guide](../../README.md#installation).

After that, reimport the existing data into Typesense by running:

```shell
docker compose exec -u www-data app php artisan upgrade:reimport-data-into-typesense
```

## What is the .keys.env file

Many Notes generates its keys on the first start and saves them in `.keys.env`, inside the `private` directory. Keep it with your backups. If you delete it, new keys are generated on the next start, and everyone is logged out.
