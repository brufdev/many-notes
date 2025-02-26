<p align="center">
    <img src="art/logo.png" width="400" />
</p>

<p align="center">
    <img alt="Latest version" src="https://img.shields.io/github/v/release/brufdev/many-notes?label=version" />
    <img alt="PHP version" src="https://img.shields.io/badge/PHP-8.4-777BB4?logo=php" />
    <img alt="Tests" src="https://img.shields.io/github/check-runs/brufdev/many-notes/main?label=tests" />
    <img alt="License" src="https://img.shields.io/github/license/brufdev/many-notes" />
</p>

Many Notes is a markdown note-taking app designed for simplicity! Easily create or import your vaults and organize your thoughts right away.

Vaults are simply storage containers for your files, and Many Notes lets you choose to keep all your files in one vault or organize them into separate vaults.

## Screenshots

![Screenshot](art/theme-light.png?raw=true)
*Light theme*

![Screenshot](art/theme-dark.png?raw=true)
*Dark theme*

## Features

- **Multiple users**: Protect your files behind authentication
- **Multiple vaults per user**: Choose how to organize your files
- **OAuth support**: Authenticate using one of the supported providers
- **File search**: Quickly find what you are looking for
- **Tree view explorer**: Fast navigation with relevant actions in the context menu
- **Smart Markdown editor**: Write your Markdown notes faster
- **Automatic saving**: Focus on writing; saving is automatic
- **Templates**: Maintain consistent formatting for your notes
- **Links/backlinks/tags**: Improve note organization by connecting related notes
- **Import/export vaults**: Easily back up and restore your vaults
- **Light/dark theme**: Automatically selected based on your OS setting
- **Mobile friendly**: Provides a similar experience to the desktop

## Installation

There are three methods to install Many Notes:

- Docker with volumes, if you prefer a simpler installation (read below)
- Docker with bind mounts, if you prefer easy access to the shared paths from the host [(read here)](docs/installation/docker-bind-mounts.md)
- Non-Docker installation, if you prefer a more manual approach [(read here)](docs/installation/non-docker.md)

### Docker with volumes

**Read the [upgrading guide](UPGRADING.md) if you are upgrading from a previous version.**

Create a `compose.yaml` file with:

```yaml
services:
  php:
    image: brufdev/many-notes:latest
    restart: unless-stopped
    volumes:
      - database:/var/www/html/database/sqlite
      - storage-logs:/var/www/html/storage/logs
      - storage-private:/var/www/html/storage/app/private
      - storage-public:/var/www/html/storage/app/public
      - storage-sessions:/var/www/html/storage/framework/sessions
    ports:
      - 80:8080

volumes:
  database:
  storage-logs:
  storage-private:
  storage-public:
  storage-sessions:
```

Feel free to change anything else if you know what you're doing, and read the customization section below before continue. Then run:

```shell
docker compose up -d
```

## Customization

To customize Many Notes, add environment variables to the `compose.yaml` file if using the Docker installation. If you chose the non-Docker installation, you should add the environment variables to a `.env` file instead.

### Custom URL (default: http://localhost)

If you change the default port from 80 or use a reverse proxy with a custom URL, make sure to configure the application URL accordingly. For example, if you change the port to 8080, add:

```yaml
environment:
  - APP_URL=http://localhost:8080
  - ASSET_URL=http://localhost:8080
```

### Custom timezone (default: UTC)

Check all available timezones [here](https://www.php.net/manual/en/timezones.php). For example, if you want to set the timezone to Amsterdam, add:

```yaml
environment:
  - APP_TIMEZONE=Europe/Amsterdam
```

### Custom upload size limit (default: 500M)

Increase the upload size limit to allow for the import of larger files. For example, if you want to increase the limit to 1 GB, add:

```yaml
environment:
  - PHP_POST_MAX_SIZE=1G
  - PHP_UPLOAD_MAX_FILE_SIZE=1G
```

### Enable OAuth providers

Many Notes supports a convenient way to authenticate with OAuth providers. Typically, these credentials may be retrieved by creating a "developer application" within the dashboard of the service you wish to use. Many Notes currently supports authentication via Facebook, Twitter, LinkedIn, Google, GitHub, GitLab, Bitbucket, Slack, Authelia, Authentik, Keycloak, and Zitadel. You can enable multiple providers simultaneously by adding the corresponding environment variables.

For example, to enable GitHub OAuth, add:

```yaml
environment:
  - GITHUB_CLIENT_ID=CLIENT_ID # change id
  - GITHUB_CLIENT_SECRET=CLIENT_SECRET # change secret
  - GITHUB_REDIRECT_URI=http://localhost/oauth/github/callback # change domain and provider
```

Authelia, Authentik, Keycloak, and Zitadel providers require additional configuration. Read the [OAuth documentation](docs/customization/oauth.md) for more information.

### Custom email service

Configure an email service to send registration and password reset emails by adding:

```yaml
environment:
  - MAIL_MAILER=smtp
  - MAIL_HOST=127.0.0.1
  - MAIL_PORT=2525
  - MAIL_USERNAME=null
  - MAIL_PASSWORD=null
  - MAIL_ENCRYPTION=null
  - MAIL_FROM_ADDRESS=hello@example.com
  - MAIL_FROM_NAME="Many Notes"
```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for the full license text.
