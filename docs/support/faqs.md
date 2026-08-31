# Frequently Asked Questions

This guide will help you find the answers to the most common questions about Many Notes.

## How to debug the application

### Application logs

Application errors are written to `storage/logs/laravel.log` inside the container. They are not shown in the container log, which only records web requests.

To watch for errors while you reproduce a problem, run:

```shell
docker compose exec php tail -f storage/logs/laravel.log
```

To see the last few errors instead:

```shell
docker compose exec php grep -a "\.ERROR" storage/logs/laravel.log | tail -5
```

### Debug mode

Debug mode shows the full error in the browser when a page crashes. It has no effect on errors the application handles itself, such as a failed login, which are only written to the log.

You can enable debug mode in your `compose.yaml` file by adding:

```yaml
environment:
  - APP_DEBUG=true
```

Turn it off again afterwards, because the error page exposes details about your installation.

### Typesense debug mode

You can enable Typesense debug mode in your `compose.yaml` file by adding:

```yaml
environment:
  - GLOG_minloglevel=2
```

The numbers of severity levels `INFO`, `WARNING`, `ERROR`, and `FATAL` are 0, 1, 2, and 3, respectively.

## Why is the build phase required when using bind mounts

The build phase may seem unnecessary when using bind mounts, but since the Docker image runs with an unprivileged user, updating permissions for files and services can only be done during the build stage. I have created a [discussion](https://github.com/brufdev/many-notes/discussions/40) to share my perspective on this topic. Feel free to join and share your thoughts.

## The search feature is not returning any results

First, make sure to mount the Typesense directory to `/var/www/html/typesense`, like is described in the [installation guide](../../README.md#installation).

After that, you need to reimport the existing data into Typesense by simply running the following command on a container shell:

```shell
php artisan upgrade:reimport-data-into-typesense
```
