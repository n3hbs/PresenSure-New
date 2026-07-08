FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

COPY . .

RUN apk add --no-cache nodejs npm

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci
RUN npm run build
RUN rm -rf node_modules

RUN php artisan view:cache

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80