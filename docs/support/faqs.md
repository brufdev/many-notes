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

## The page loads without styles behind a reverse proxy

Many Notes builds the address of its styles and scripts from the address you used to open it. Behind a reverse proxy serving HTTPS, the proxy must tell Many Notes that the original request used HTTPS. Otherwise, the styles are requested over HTTP and the browser blocks them.

Most reverse proxies, such as Traefik and Caddy, do this by default. With nginx, add the `X-Forwarded-Proto` header to your configuration:

```nginx
location / {
    proxy_pass http://127.0.0.1:6683;
    proxy_http_version 1.1;
    proxy_set_header Host $http_host;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
}
```

The last two headers keep live updates working. If you can't change the proxy configuration, set the address of the styles and scripts in your `compose.yaml` instead:

```yaml
environment:
  - ASSET_URL=https://notes.example.com
```

## The search feature is not returning any results

First, make sure to mount the Typesense directory to `/var/www/html/typesense`, like is described in the [installation guide](../../README.md#installation).

After that, reimport the existing data into Typesense by running:

```shell
docker compose exec -u www-data app php artisan upgrade:reimport-data-into-typesense
```

## What is the .keys.env file

Many Notes generates its keys on the first start and saves them in `.keys.env`, inside the `private` directory. Keep it with your backups. If you delete it, new keys are generated on the next start, and everyone is logged out.
