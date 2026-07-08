FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

COPY . .

# Install Node.js and npm for Vite build
RUN apk add --no-cache nodejs npm

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Build React/Inertia/Vite assets
RUN npm install
RUN npm run build

# Laravel optimization
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80