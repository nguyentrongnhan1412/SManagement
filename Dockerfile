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

# Copy all project files to the Apache root
COPY . /var/www/html/

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite