# Installation guide (Docker with a different database)

**Read the [upgrading guide](../../UPGRADING.md) if you are upgrading from a previous version.**

Many Notes uses SQLite by default but supports other databases like MariaDB, MySQL, and PostgreSQL. This guide will use MariaDB, but you can easily adapt it to one of the other databases. The only difference is the `DB_CONNECTION` environment variable, which should be set to `mariadb` for MariaDB, `mysql` for MySQL, and `pgsql` for PostgreSQL.

## Instructions

Create a `compose.yaml` file with:

```yaml
services:
  php:
    image: brufdev/many-notes:latest
    restart: unless-stopped
    environment:
      - APP_URL=http://localhost # address used to access the application
      - DB_CONNECTION=mariadb
      - DB_HOST=many-notes-mariadb-1
      - DB_PORT=3306
      - DB_DATABASE=manynotes
      - DB_USERNAME=user
      - DB_PASSWORD=USER_PASSWORD # change password
    volumes:
      - logs:/var/www/html/storage/logs
      - private:/var/www/html/storage/app/private
      - typesense:/var/www/html/typesense
    ports:
      - 80:8080
  mariadb:
    image: mariadb:11
    restart: unless-stopped
    environment:
      - MARIADB_ROOT_PASSWORD=ROOT_PASSWORD # change password
      - MARIADB_DATABASE=manynotes
      - MARIADB_USER=user
      - MARIADB_PASSWORD=USER_PASSWORD # change password
    volumes:
      - database:/var/lib/mysql

volumes:
  database:
  logs:
  private:
  typesense:
```

Make sure to change the passwords. Feel free to change anything else if you know what you're doing, and read the [customization section](../../README.md#customization) before continuing. Then run:

```shell
docker compose up -d
```
