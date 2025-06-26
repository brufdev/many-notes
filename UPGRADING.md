# Upgrade guide

This guide will walk you through the necessary changes to upgrade from a previous version of Many Notes. Be sure to back up your data, especially before updates. If you need further help, consult the [FAQs](docs/support/faqs.md), or if your question isn't answered there, please open an issue on GitHub.

## Upgrading from any version below 0.9

Version 0.9 introduces an improved search feature powered by Typesense, which requires mounting a new directory to persist Typesense data when the container is down. Additionally, the `compose.yaml` file has been simplified, as the `ASSET_URL` environment variable and the `public` and `sessions` directories are no longer needed.

If you upgraded without mounting the Typesense directory, the search feature will return no results after the next container restart. Please refer to the [FAQs](docs/support/faqs.md) for instructions on how to resolve this issue.

## Upgrading from any version below 0.7

Version 0.7 changes the location of the SQLite database file to support both Docker volumes and bind mounts. The database file is now located in the `database/sqlite` directory.

## Upgrading from any version below 0.4

Version 0.4 introduces **breaking changes** in how the vaults are saved. Stop the containers and back up your data before proceeding.

Notes were only saved in the database, but starting from v0.4, they are also saved in the filesystem. In case of database corruption, the `private` directory will now contain a complete copy of all vaults.

The installation instructions now recommend using bind mounts instead of Docker volumes, and SQLite instead of MariaDB. This change is intended to simplify the installation process. However, you can still use Docker volumes or MariaDB if you prefer.

The best way to upgrade is to export all vaults from the UI in v0.3 and then import them after a fresh installation of the new version. If you updated the Docker image before exporting the vaults, you can downgrade to v0.3 by using the corresponding tag.
