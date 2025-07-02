FROM dunglas/frankenphp:latest

# 1. Install required extensions
RUN install-php-extensions pcntl pdo_pgsql opcache intl zip exif

# 2. Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    nodejs \
    npm \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Copy only composer files first (for caching)
WORKDIR /app
COPY composer.json composer.lock ./
COPY package.json ./

# 5. Install dependencies (cached unless composer files change)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 6. Copy the rest of the application
COPY . .

ENV APP_NAME="Supply Catena"
ENV APP_ENV=production
ENV APP_KEY=base64:OS9J7O4DX5zMNUhASURJetb239q9dIDIKlmkfWSIB4k=
ENV APP_DEBUG=false
ENV APP_URL="https://supplycatena.com"
ENV ASSET_URL=${APP_URL}
ENV APP_TIMEZONE=UTC
ENV APP_LOCALE=en
ENV APP_FALLBACK_LOCALE=en
ENV LOG_CHANNEL=stderr
ENV LOG_DEPRECATIONS_CHANNEL=null
ENV LOG_LEVEL=debug
ENV DB_CONNECTION=pgsql
ENV DB_HOST=ep-spring-paper-a8b8p0uw.eastus2.azure.neon.tech
ENV DB_PORT=5432
ENV DB_DATABASE=supplycatena
ENV DB_USERNAME=supplycatena_owner
ENV DB_PASSWORD=npg_2t9GPqIXNCQT
ENV TURSO_DATABASE_URL="libsql://supplycatena-suwi-lanji.aws-ap-northeast-1.turso.io"
ENV TURSO_AUTH_TOKEN="eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTEyNzA5NjksImlkIjoiNTBhMzM0NzctODhjZC00ODM3LWFkZjktYWU0YzI0Y2FjMmY1IiwicmlkIjoiNjdmZmFlNzgtZTBiNi00NjY1LWE0OTYtNGRlY2RlN2I4YmMwIn0.De8eaFv3HnX7_cZTtGZXUNI2a0ZmyBQgtopD3BlN1-F2B39yCJWf0lVVjPqrBJxZmsVtKTRfnTMRohNv26AhCQ"
ENV TURSO_SYNC_INTERVAL=300
ENV DB_AUTH_TOKEN="eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NTEyNzA5NjksImlkIjoiNTBhMzM0NzctODhjZC00ODM3LWFkZjktYWU0YzI0Y2FjMmY1IiwicmlkIjoiNjdmZmFlNzgtZTBiNi00NjY1LWE0OTYtNGRlY2RlN2I4YmMwIn0.De8eaFv3HnX7_cZTtGZXUNI2a0ZmyBQgtopD3BlN1-F2B39yCJWf0lVVjPqrBJxZmsVtKTRfnTMRohNv26AhCQ"
ENV DB_SYNC_URL="libsql://supplycatena-suwi-lanji.aws-ap-northeast-1.turso.io"
ENV DB_SYNC_INTERVAL=5
ENV DB_READ_YOUR_WRITES=true
ENV DB_ENCRYPTION_KEY=""
ENV DB_REMOTE_ONLY=false
ENV CACHE_DRIVER=database
ENV SESSION_DRIVER=database
ENV SESSION_LIFETIME=120
ENV SESSION_ENCRYPT=false
ENV QUEUE_CONNECTION=sync
ENV REDIS_HOST=127.0.0.1
ENV REDIS_PASSWORD=null
ENV REDIS_PORT=6379
ENV MAIL_MAILER=resend
ENV MAIL_FROM_ADDRESS="onboarding@resend.dev"
ENV MAIL_FROM_NAME="${APP_NAME}"
ENV RESEND_API_KEY=re_73vhATde_EJ2qUzKzd6vV4XoHRL68AMyH
ENV VITE_APP_NAME="${APP_NAME}"
ENV ZRA_TPIN=2179235933
ENV ZRA_BHF_ID=000
ENV ZRA_API_URL=http://209.97.129.148:9810/zrasandboxvsdc/
ENV ZRA_DVC_SRL_NO=2179235933_VSDC
ENV FRANKENPHP_CONFIG="worker"
ENV OCTANE_SERVER=frankenphp
ENV FILESYSTEM_DISK=cloudinary
ENV FILAMENT_FILESYSTEM_DISK=cloudinary
ENV CLOUDINARY_URL=cloudinary://586283381672664:hTum1a6u7Xf-rWigOC61dRKbjNU@do3ne4vzy

RUN php artisan config:clear

RUN php artisan filament:optimize-clear

RUN php artisan optimize:clear

RUN php artisan event:clear

RUN php artisan route:clear

RUN php artisan view:cache

RUN php artisan filament:optimize

# 9. Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# 10. Health check endpoint
HEALTHCHECK --interval=30s --timeout=3s \
    CMD curl -f http://localhost:8080/health || exit 1

# 11. Entrypoint with proper FrankenPHP configuration
ENTRYPOINT ["php", "artisan", "octane:frankenphp", "--port=8080"]
