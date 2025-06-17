# Installation guide (Docker with a different database)

**Read the [upgrading guide](../../UPGRADING.md) if you are upgrading from a previous version.**

Many Notes uses SQLite by default but supports other databases like MariaDB, MySQL, and PostgreSQL. The main difference is the `DB_CONNECTION` environment variable, which should be set to `mariadb` for MariaDB, `mysql` for MySQL, and `pgsql` for PostgreSQL. This guide will use MySQL, but you can easily adapt it to one of the other databases.

## Instructions

Create a `compose.yaml` file with:

```yaml
services:
  php:
    image: brufdev/many-notes:latest
    restart: unless-stopped
    environment:
      - APP_URL=http://localhost # address used to access the application
      - DB_CONNECTION=mysql
      - DB_HOST=many-notes-mysql-1
      - DB_PORT=3306
      - DB_DATABASE=manynotes
      - DB_USERNAME=user
      - DB_PASSWORD=USER_PASSWORD # mysql user password
    volumes:
      - logs:/var/www/html/storage/logs
      - private:/var/www/html/storage/app/private
      - typesense:/var/www/html/typesense
    ports:
      - 80:8080
  mysql:
    image: mysql:9
    restart: unless-stopped
    environment:
      - MYSQL_ROOT_PASSWORD=ROOT_PASSWORD # mysql root password
      - MYSQL_DATABASE=manynotes
      - MYSQL_USER=user
      - MYSQL_PASSWORD=USER_PASSWORD # mysql user password
    volumes:
      - database:/var/lib/mysql

volumes:
  database:
  logs:
  private:
  typesense:
```

Make sure to change the passwords and the address used to access the application. Feel free to change anything else if you know what you're doing, and read the [customization section](../../README.md#customization) before continuing. Then run:

```shell
docker compose up -d
```
