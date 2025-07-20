FROM php:8.2-apache

# Install PostgreSQL PDO extension
RUN docker-php-ext-install pdo pdo_pgsql

# Copy all project files to the Apache root
COPY . /var/www/html/

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite
