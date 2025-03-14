# Installation guide (Docker with a different database)

**Read the [upgrading guide](../../UPGRADING.md) if you are upgrading from a previous version.**

Many Notes uses SQLite by default but supports other databases like MariaDB, MySQL, and PostgreSQL. This guide will use MariaDB, but you can easily adapt it to one of the other databases.

## Instructions

Create a `compose.yaml` file with:

```yaml
services:
  php:
    image: brufdev/many-notes:latest
    restart: unless-stopped
    environment:
      - DB_CONNECTION=mariadb
      - DB_HOST=many-notes-mariadb-1
      - DB_PORT=3306
      - DB_DATABASE=manynotes
      - DB_USERNAME=user
      - DB_PASSWORD=USER_PASSWORD # change password
    volumes:
      - storage-logs:/var/www/html/storage/logs
      - storage-private:/var/www/html/storage/app/private
      - storage-public:/var/www/html/storage/app/public
      - storage-sessions:/var/www/html/storage/framework/sessions
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
  storage-logs:
  storage-private:
  storage-public:
  storage-sessions:
```

Make sure to change the passwords. Feel free to change anything else if you know what you're doing, and read the [customization section](../../README.md#customization) before continuing. Then run:

```shell
docker compose up -d
```
