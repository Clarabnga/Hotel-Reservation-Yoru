FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM dunglas/frankenphp:1-php8.3-bookworm AS runtime

RUN install-php-extensions pdo_pgsql mbstring xml curl bcmath zip intl pcntl opcache redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/start-container.sh /usr/local/bin/start-container

RUN composer install \
      --no-dev \
      --no-interaction \
      --no-progress \
      --prefer-dist \
      --classmap-authoritative \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache /data /config \
    && ln -s /app/storage/app/public /app/public/storage \
    && chown -R www-data:www-data storage bootstrap/cache /data /config \
    && chmod +x /usr/local/bin/start-container \
    && rm -rf resources/css resources/js \
    && rm -f package.json package-lock.json postcss.config.js tailwind.config.js vite.config.js

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=warning \
    PORT=10000

USER www-data
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/start-container"]
