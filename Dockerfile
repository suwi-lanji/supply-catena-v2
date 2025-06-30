# ---- Base Stage ----
# Use the lightweight Alpine version of the official PHP 8.2 FPM image
FROM php:8.2-fpm-alpine as base

# Set working directory
WORKDIR /var/www/html

# Install system dependencies required for Laravel and common extensions
# Using apk for Alpine Linux
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libpq-dev \
    oniguruma-dev \
    libxml2-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    gd \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pcntl \
    bcmath \
    opcache \
    exif \
    zip \
    intl

# Get the latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ---- Dependencies Stage ----
# This stage only installs Composer dependencies to leverage Docker's cache
FROM base as dependencies

# Copy only composer files
COPY database/ database/
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader --no-dev

# ---- Final Production Image ----
FROM base as final

# Copy application code from your local machine
COPY . .

# Copy installed dependencies from the 'dependencies' stage
COPY --from=dependencies /var/www/html/vendor/ /var/www/html/vendor/

# Set correct file permissions for Laravel
# This is crucial for logs and caching to work
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Generate Laravel's optimized files for production
RUN composer dump-autoload --optimize && \
    php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan event:cache && \
    php artisan route:cache && \
    php artisan view:cache

# --- Nginx & Supervisor Configuration ---
# Copy Nginx configuration file
COPY .docker/nginx.conf /etc/nginx/nginx.conf

# Copy Supervisor configuration file (manages Nginx and PHP-FPM)
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy the entrypoint script that will run when the container starts
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Cloud Run expects the container to listen on port 8080
EXPOSE 8080

# The entrypoint script will start all the necessary services
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
