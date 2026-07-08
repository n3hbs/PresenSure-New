FROM node:22-alpine AS assets

WORKDIR /app

COPY . .

RUN npm ci
RUN npm run build

FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV WEBROOT=/var/www/html/public

COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx/default.conf /tmp/nginx-default.conf

RUN if [ -d /etc/nginx/sites-available ]; then cp /tmp/nginx-default.conf /etc/nginx/sites-available/default.conf; fi && \
    if [ -d /etc/nginx/sites-enabled ]; then cp /tmp/nginx-default.conf /etc/nginx/sites-enabled/default.conf; fi && \
    if [ -d /etc/nginx/conf.d ]; then cp /tmp/nginx-default.conf /etc/nginx/conf.d/default.conf; fi

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan view:cache

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80
