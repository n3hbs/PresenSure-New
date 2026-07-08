FROM node:22-alpine AS assets

WORKDIR /app

COPY . .

RUN npm ci
RUN npm run build

FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV WEBROOT=/var/www/html/public

RUN set -eux; \
    for file in /etc/nginx/conf.d/*.conf /etc/nginx/sites-available/* /etc/nginx/sites-enabled/*; do \
        [ -f "$file" ] || continue; \
        sed -i 's#root /var/www/html;#root /var/www/html/public;#g' "$file"; \
        sed -i 's#try_files $uri $uri/ =404;#try_files $uri $uri/ /index.php?$query_string;#g' "$file"; \
        sed -i 's#try_files $uri =404;#try_files $uri /index.php?$query_string;#g' "$file"; \
    done

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan view:cache

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80
