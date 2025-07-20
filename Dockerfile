FROM php:8.2-apache

# Install system dependencies and PostgreSQL dev libraries
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        git \
        unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files and install dependencies first (for better Docker caching)
COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html
RUN composer install --no-dev --no-interaction --prefer-dist

# Now copy the rest of the application
COPY . /var/www/html/

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite