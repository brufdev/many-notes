FROM node:26.13.1-trixie-slim AS node

FROM typesense/typesense:30.2 AS typesense

FROM serversideup/php:8.5-fpm-nginx-trixie-v5.2.1 AS php

# Typesense-stripped: Typesense without debug symbols
FROM php AS typesense-stripped
USER root
COPY --from=typesense /opt/typesense-server /opt/typesense-server
RUN strip /opt/typesense-server

# Base: PHP runtime shared by every target
FROM php AS base
USER root
ENV GLOG_minloglevel=1
COPY --from=typesense-stripped --chmod=755 /opt/typesense-server /opt/typesense-server
COPY --chmod=755 ./docker/nginx/reverb.conf /etc/nginx/server-opts.d/reverb.conf
COPY --chmod=755 ./docker/s6-overlay/queue/ /etc/s6-overlay/s6-rc.d/queue
COPY --chmod=755 ./docker/s6-overlay/reverb/ /etc/s6-overlay/s6-rc.d/reverb
COPY --chmod=755 ./docker/s6-overlay/typesense/ /etc/s6-overlay/s6-rc.d/typesense
COPY --chmod=755 ./docker/s6-overlay/user/ /etc/s6-overlay/user-bundles.d/user/contents.d
COPY --chmod=755 ./docker/entrypoint /etc/entrypoint.d
STOPSIGNAL SIGTERM

# Base-node: Node toolchain on top of PHP
FROM base AS base-node
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Development: source is bind-mounted, www-data matches the host user
FROM base-node AS development
ARG PUID=1000
ARG PGID=1000
ENV PHP_MEMORY_LIMIT=1G \
    XDEBUG_MODE=off
COPY --chmod=755 ./docker/s6-overlay-dev/queue/run /etc/s6-overlay/s6-rc.d/queue/run
COPY --chmod=755 ./docker/entrypoint-dev/ /etc/entrypoint.d/
RUN install-php-extensions xdebug \
    && docker-php-serversideup-set-id www-data "$PUID":"$PGID" \
    && docker-php-serversideup-set-file-permissions --owner "$PUID":"$PGID" --service nginx
USER www-data

# Build: install dependencies, compile assets, drop build-only files
FROM base-node AS build
USER www-data
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN --mount=type=cache,target=/composer/cache,uid=33,gid=33 \
    composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist
COPY --chown=www-data:www-data package.json package-lock.json ./
RUN --mount=type=cache,target=/var/www/.npm,uid=33,gid=33 \
    npm ci
COPY --chown=www-data:www-data . .
RUN cp .env.example .env \
    && composer dump-autoload --no-dev --no-interaction --classmap-authoritative \
    && npm run build \
    && mkdir -p database/sqlite \
    && find resources/js -mindepth 1 -maxdepth 1 ! -name pages -exec rm -rf {} + \
    && rm -rf node_modules docker resources/css \
        package.json package-lock.json tsconfig.json vite.config.js

# Production: code owned by root, www-data only owns what it writes
FROM base AS production
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_STACK=stderr,daily \
    MAIL_LOG_CHANNEL=daily \
    PHP_OPCACHE_ENABLE=1 \
    PHP_POST_MAX_SIZE="500M" \
    PHP_UPLOAD_MAX_FILE_SIZE="500M" \
    HEALTHCHECK_PATH=/up \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_STORAGE_LINK=false
COPY --from=build --chown=root:root /var/www/html /var/www/html
RUN cd /var/www/html \
    && chown root:root . \
    && chown -R www-data:www-data storage bootstrap/cache database/sqlite typesense .env \
    && chmod 600 .env
COPY --chmod=755 ./docker/bin/many-notes-entrypoint /usr/local/bin/many-notes-entrypoint
ARG APP_VERSION=dev
ENV APP_VERSION=$APP_VERSION
ENTRYPOINT ["many-notes-entrypoint"]
CMD ["/init"]
