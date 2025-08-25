# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies & unzip (needed for Composer)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Copy project files to container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies via Composer
RUN composer install

# Expose port 80
EXPOSE 80
