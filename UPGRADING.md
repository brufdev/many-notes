# Upgrade guide

## Upgrading from any version below 0.7

Version 0.7 changes the location of the SQLite database file to support both Docker volumes and bind mounts. The database file is now located in the `database/sqlite` directory.

## Upgrading from any version below 0.4

Version 0.4 introduces **breaking changes** in how the vaults are saved. Stop the containers and back up your data before proceeding.

Notes were only saved in the database, but starting from v0.4, they are also saved in the filesystem. In case of database corruption, the `storage-private` directory will now contain a complete copy of all vaults.

The installation instructions now recommend using bind mounts instead of Docker volumes, and SQLite instead of MariaDB. This change is intended to simplify the installation process. However, you can still use Docker volumes or MariaDB if you prefer.

The best way to upgrade is to export all vaults from the UI in v0.3 and then import them after a fresh installation of the new version. If you updated the Docker image before exporting the vaults, you can downgrade to v0.3 by using the corresponding tag.

If you need help, please open an issue on GitHub.
