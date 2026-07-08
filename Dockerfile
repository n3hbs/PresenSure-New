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

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan view:cache

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80
