# Frequently Asked Questions

This guide will help you find the answers to the most common questions about Many Notes.

<details>
<summary><strong>How to debug the application</strong></summary>
<br/>

You can enable debug mode in your `compose.yaml` file by adding:

```yaml
environment:
  - APP_DEBUG=true
```

You can also enable Typesense debug mode in your `compose.yaml` file by adding:

```yaml
environment:
  - GLOG_minloglevel=2
```

The numbers of severity levels `INFO`, `WARNING`, `ERROR`, and `FATAL` are 0, 1, 2, and 3, respectively.
</details>

<details>
<summary><strong>Why is the build phase required when using bind mounts</strong></summary>
<br/>

The build phase may seem unnecessary when using bind mounts, but since the Docker image runs with an unprivileged user, updating permissions for files and services can only be done during the build stage. I have created a [discussion](https://github.com/brufdev/many-notes/discussions/40) to share my perspective on this topic. Feel free to join and share your thoughts.
</details>

<details>
<summary><strong>The search feature is not returning any results</strong></summary>
<br/>

First, make sure to mount the Typesense directory to `/var/www/html/typesense`, like is described in the [installation guide](../../README.md#installation).

After that, you need to reimport the existing data into Typesense by simply running the following command on a container shell:

```shell
php artisan upgrade:reimport-data-into-typesense
```
</details>
