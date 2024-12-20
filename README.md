# Many Notes

Many Notes is a markdown note-taking app designed for simplicity! Easily create or import your vaults and organize your thoughts right away.

Vaults are simply storage containers for your files, and Many Notes provides you with the flexibility to keep everything in one vault or to separate your files into multiple vaults.

## Screenshots

![Screenshot](.github/screenshots/theme-light.gif?raw=true)
*Light theme*

![Screenshot](.github/screenshots/theme-dark.gif?raw=true)
*Dark theme*

## Features

- Multiple users
- Multiple vaults per user
- File search
- Tree view explorer with context menu
- Smart markdown editor
- Import/export vaults
- Light/dark theme (automatically selected by your OS setting)
- Mobile friendly

## Installation (Docker)

Create a new directory called `many-notes`. Inside this directory, create a file named `compose.yaml` and paste the following content:

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
      - storage-public:/var/www/html/storage/app/public
      - storage-private:/var/www/html/storage/app/private
      - storage-sessions:/var/www/html/storage/framework/sessions
      - storage-logs:/var/www/html/storage/logs
    ports:
      - 80:8080
  mariadb:
    image: mariadb:11.6
    restart: unless-stopped
    environment:
      - MARIADB_ROOT_PASSWORD=ROOT_PASSWORD # change password
      - MARIADB_DATABASE=manynotes
      - MARIADB_USER=user
      - MARIADB_PASSWORD=USER_PASSWORD # change password
    volumes:
      - mariadb-data:/var/lib/mysql

volumes:
  storage-public:
  storage-private:
  storage-sessions:
  storage-logs:
  mariadb-data:
```

Make sure to change the passwords and feel free to customize anything else if you know what you're doing. Read the customization section below before continue. Then run:

```shell
docker compose up -d
```

## Customization

You can customize Many Notes by adding environment variables to the `compose.yaml` file.

#### Custom URL (default: http://localhost)

If you change the default port from 80 or use a reverse proxy with a custom URL, make sure to configure the application URL accordingly. For example, if you change the port to 8080, set:

```yaml
- APP_URL=http://localhost:8080
- ASSET_URL=http://localhost:8080
```

#### Custom timezone (default: UTC)

Check all available timezones [here](https://www.php.net/manual/en/timezones.php). For example, if you want to set the timezone to Amsterdam, add:

```yaml
- APP_TIMEZONE=Europe/Amsterdam
```

#### Custom upload size limit (default: 500M)

Increase the upload size limit to allow for the import of larger files. For example, if you want to increase the limit to 1 GB, add:

```yaml
- PHP_POST_MAX_SIZE=1G
- PHP_UPLOAD_MAX_FILE_SIZE=1G
```

#### Custom email service

Configure an email service to send registration and password reset emails by adding:

```yaml
- MAIL_MAILER=smtp
- MAIL_HOST=127.0.0.1
- MAIL_PORT=2525
- MAIL_USERNAME=null
- MAIL_PASSWORD=null
- MAIL_ENCRYPTION=null
- MAIL_FROM_ADDRESS=hello@example.com
- MAIL_FROM_NAME="Many Notes"
```

## Backup and restore

All your non-note files are saved in the `storage-private` volume, while your notes are stored in the database.

#### Backup database

To back up your database, run:

```shell
docker exec many-notes-mariadb-1 mariadb-dump --all-databases -uroot -p"$MARIADB_ROOT_PASSWORD" > ./backup-many-notes-`date +%Y-%m-%d`.sql
```

#### Restore database

To restore your database from a backup, run:

```shell
docker exec -i many-notes-mariadb-1 sh -c 'exec mariadb -uroot -p"$MARIADB_ROOT_PASSWORD"' < ./$BACKUP_FILE_NAME.sql
```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for the full license text.
