# Installation guide (non-Docker)

**Read the [upgrading guide](../../UPGRADING.md) if you are upgrading from a previous version.**

The Docker method is recommended because it is faster and simpler to set up. However, if you prefer a non-Docker installation, here are the full instructions.

## Requirements

PHP 8.4+, Composer, npm and Git

## Instructions

Clone the project:

```shell
git clone https://github.com/brufdev/many-notes.git
```

Install Composer dependencies

```shell
composer install --no-dev --optimize-autoloader
```

Install npm dependencies

```shell
npm install
```

Run the npm build

```shell
npm run build
```

Create the SQLite database

```shell
touch database/sqlite/database.sqlite
```

Create .env file

```shell
cp .env.example .env
```

Generate application key

```shell
php artisan key:generate
```

Create caches to optimize the application

```shell
php artisan optimize
```

Create the symbolic link for Many Notes public storage

```shell
php artisan storage:link
```

Run the database migrations

```shell
php artisan migrate
```

Run the upgrade command

```shell
php artisan upgrade:run
```

The way to customize Many Notes in a non-Docker installation is to add/update the variables in the `.env` file at the root of the project. The only exception is customizing the upload size limit, which needs to be changed in your PHP settings. Read the [customization section](../../README.md#customization) before continuing.
