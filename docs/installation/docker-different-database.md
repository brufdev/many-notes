# Installation guide (Docker with a different database)

**Read the [upgrading guide](../../UPGRADING.md) if you are upgrading from a previous version.**

Many Notes uses SQLite by default but also supports PostgreSQL, MariaDB, and MySQL. This guide uses PostgreSQL. To use MySQL or MariaDB instead, read the [section below](#mysql-or-mariadb).

## Instructions

Create a `compose.yaml` file with:

```yaml
services:
  app:
    image: brufdev/many-notes:latest
    restart: unless-stopped
    environment:
      - APP_URL=http://localhost:6683 # address used to access the application
      - PUID=1000 # host user id
      - PGID=1000 # host group id
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=manynotes
      - DB_USERNAME=manynotes
      - DB_PASSWORD=DB_PASSWORD # database password
    volumes:
      - ./logs:/var/www/html/storage/logs
      - ./private:/var/www/html/storage/app/private
      - ./typesense:/var/www/html/typesense
    ports:
      - 6683:8080
    depends_on:
      postgres:
        condition: service_healthy
  postgres:
    image: postgres:19
    restart: unless-stopped
    environment:
      - POSTGRES_DB=manynotes
      - POSTGRES_USER=manynotes
      - POSTGRES_PASSWORD=DB_PASSWORD # database password
    volumes:
      - ./database:/var/lib/postgresql
    healthcheck:
      test: ["CMD", "pg_isready", "-h", "127.0.0.1", "-U", "manynotes", "-d", "manynotes"]
      interval: 5s
      timeout: 3s
      retries: 5
```

Many Notes runs as the user and group set by `PUID` and `PGID`, so the shared folders belong to your host user. Run `id -u` and `id -g` on the host to find your IDs. The `database` folder is managed by the database, which writes it as its own user. The folders are created next to `compose.yaml` on the first start.

Make sure to change the IDs, the database password in both places, and the address used to access the application. Feel free to change anything else if you know what you're doing, and read the [customization section](../../README.md#customization) before continuing. Then run:

```shell
docker compose up -d
```

## MySQL or MariaDB

Replace the `postgres` service with one of the services below, and rename `postgres` under `depends_on` to the new service name. In the `app` service, change the database settings to:

```yaml
      - DB_CONNECTION=mysql # or mariadb
      - DB_HOST=mysql # or mariadb
      - DB_PORT=3306
```

For MySQL:

```yaml
  mysql:
    image: mysql:9
    restart: unless-stopped
    environment:
      - MYSQL_DATABASE=manynotes
      - MYSQL_USER=manynotes
      - MYSQL_PASSWORD=DB_PASSWORD # database password
      - MYSQL_RANDOM_ROOT_PASSWORD=1
    volumes:
      - ./database:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "127.0.0.1", "--silent"]
      interval: 5s
      timeout: 3s
      retries: 5
```

For MariaDB:

```yaml
  mariadb:
    image: mariadb:12
    restart: unless-stopped
    environment:
      - MARIADB_DATABASE=manynotes
      - MARIADB_USER=manynotes
      - MARIADB_PASSWORD=DB_PASSWORD # database password
      - MARIADB_RANDOM_ROOT_PASSWORD=1
    volumes:
      - ./database:/var/lib/mysql
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 5s
      timeout: 3s
      retries: 5
```
