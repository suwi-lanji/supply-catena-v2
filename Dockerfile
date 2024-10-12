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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql pdo_sqlite mbstring exif pcntl bcmath opcache intl zip

# Set Composer Superuser
ENV COMPOSER_SUPERUSER 1

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application files
COPY . .

# Install dependencies
RUN composer install

# Expose port 8000 and start php-fpm server
EXPOSE 8000

# Run Laravel migrations
RUN php artisan migrate

# Link storage for Laravel
#RUN php artisan storage:link

# Command to run the Laravel development server
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
