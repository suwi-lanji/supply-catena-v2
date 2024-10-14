# Use the official PHP image as a base
FROM php:8.2

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    sqlite3 \
    libsqlite3-dev \
    curl \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql pdo_sqlite mbstring exif pcntl bcmath opcache intl zip

# Install the FTP extension
RUN apt-get install -y libssh2-1-dev libssh2-1 \
    && docker-php-ext-install ftp

# Set Composer Superuser
ENV COMPOSER_SUPERUSER 1

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application files
COPY . .

# Install PHP dependencies (Composer packages)
RUN composer install --no-dev --optimize-autoloader

# Expose port 8000
EXPOSE 8000

# Ensure correct permissions for Laravel storage and cache folders
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create a Supervisor configuration for Laravel
RUN echo '[program:laravel-worker]\n' \
    'process_name=%(program_name)s_%(process_num)02d\n' \
    'command=php /var/www/html/artisan queue:work --sleep=3 --tries=3\n' \
    'autostart=true\n' \
    'autorestart=true\n' \
    'user=www-data\n' \
    'numprocs=1\n' \
    'redirect_stderr=true\n' \
    'stdout_logfile=/var/www/html/storage/logs/worker.log\n' > /etc/supervisor/conf.d/laravel-worker.conf

# Run the Laravel development server or php-fpm (if needed)
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
