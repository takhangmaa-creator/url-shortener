FROM php:8.4-fpm

# dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    supervisor \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# adjust user permission and group
RUN usermod --uid 1000 www-data
RUN groupmod --gid 1000 www-data

# Copy all application files with correct ownership
COPY --chown=www-data:www-data . .

USER www-data

# Add at the end of PHP Dockerfile
COPY docker/php/fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

# Run the entrypoint file
ENTRYPOINT [ "sh", "entrypoint.sh" ]
