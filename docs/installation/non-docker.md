# Installation guide

Here are the full instructions for a non-Docker installation.

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
