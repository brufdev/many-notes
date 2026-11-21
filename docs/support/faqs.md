# Frequently Asked Questions

This guide will help you find the answers to the most common questions about Many Notes.

## How to debug the application

Application errors are written to the container log, and also saved in the `logs` folder, one file per day for the last 14 days. To see them, run:

```shell
docker compose logs app
```

To watch for new errors while you reproduce a problem, add `-f`:

```shell
docker compose logs -f app
```

## The search feature is not returning any results

First, make sure to mount the Typesense directory to `/var/www/html/typesense`, like is described in the [installation guide](../../README.md#installation).

After that, reimport the existing data into Typesense by running:

```shell
docker compose exec -u www-data app php artisan upgrade:reimport-data-into-typesense
```

## What is the .keys.env file

Many Notes generates its keys on the first start and saves them in `.keys.env`, inside the `private` directory. Keep it with your backups. If you delete it, new keys are generated on the next start, and everyone is logged out.
