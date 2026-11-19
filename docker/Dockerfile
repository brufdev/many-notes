FROM typesense/typesense:30.2 AS typesense

FROM serversideup/php:8.4-fpm-nginx-trixie-v5.0.2 AS base
USER root
RUN install-php-extensions intl
# Copy Typesense executable
COPY --from=typesense --chmod=755 /opt/typesense-server /opt/typesense-server
# Copy custom Nginx configuration
COPY --chmod=755 ./docker/nginx/reverb.conf /etc/nginx/server-opts.d/reverb.conf
# Copy S6 service definition files
COPY --chmod=755 ./docker/s6-overlay/queue/ /etc/s6-overlay/s6-rc.d/queue
COPY --chmod=755 ./docker/s6-overlay/reverb/ /etc/s6-overlay/s6-rc.d/reverb
COPY --chmod=755 ./docker/s6-overlay/typesense/ /etc/s6-overlay/s6-rc.d/typesense
COPY --chmod=755 ./docker/s6-overlay/user/ /etc/s6-overlay/user-bundles.d/user/contents.d
# Copy entrypoint scripts
COPY --chmod=755 ./docker/entrypoint /etc/entrypoint.d

FROM base AS development
ARG USER_ID
ARG GROUP_ID
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    docker-php-serversideup-set-id www-data "$USER_ID":"$GROUP_ID" && \
    docker-php-serversideup-set-file-permissions --owner "$USER_ID":"$GROUP_ID" --service nginx
ENV PHP_MEMORY_LIMIT=1G \
    XDEBUG_MODE=coverage
STOPSIGNAL SIGTERM
USER www-data

FROM base AS production
ENV APP_ENV=production \
    APP_DEBUG=false \
    GLOG_minloglevel=3 \
    PHP_OPCACHE_ENABLE=1 \
    PHP_POST_MAX_SIZE="500M" \
    PHP_UPLOAD_MAX_FILE_SIZE="500M" \
    AUTORUN_ENABLED=true
COPY --chown=www-data:www-data . /var/www/html
COPY --chown=www-data:www-data --chmod=600 .env.example /var/www/html/.env
STOPSIGNAL SIGTERM
USER www-data
