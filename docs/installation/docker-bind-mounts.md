# Installation guide (Docker with bind mounts)

**Read the [upgrading guide](../../UPGRADING.md) if you are upgrading from a previous version.**

Many Notes must have the necessary permissions to access the shared paths. Since this image runs with an unprivileged user, the host user IDs must be added during the build phase.

## Instructions

First, create a new directory called `many-notes` with the following structure:

```
many-notes/
├── database/
├── logs/
├── private/
├── typesense/
```

Next, create a `Dockerfile` file with:

```Dockerfile
FROM brufdev/many-notes:latest
USER root
ARG UID
ARG GID
RUN docker-php-serversideup-set-id www-data $UID:$GID && \
    docker-php-serversideup-set-file-permissions --owner $UID:$GID --service nginx
USER www-data
```

Finally, create a `compose.yaml` file with:

```yaml
services:
  php:
    build:
      context: .
      args:
        UID: USER_ID # change id
        GID: GROUP_ID # change id
    restart: unless-stopped
    environment:
      - APP_URL=http://localhost # address used to access the application
    volumes:
      - ./database:/var/www/html/database/sqlite
      - ./logs:/var/www/html/storage/logs
      - ./private:/var/www/html/storage/app/private
      - ./typesense:/var/www/html/typesense
    ports:
      - 80:8080
```

Make sure to update the IDs to match the host user IDs. Feel free to change anything else if you know what you're doing, and read the [customization section](../../README.md#customization) before continuing. Then run:

```shell
docker compose up -d
```
