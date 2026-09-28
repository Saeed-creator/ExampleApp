FROM php:8.3-apache

# Install system dependencies and PostgreSQL PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application
COPY . .

# Install Laravel dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Point Apache to Laravel's public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Allow Laravel .htaccess rewrite rules
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
    /etc/apache2/apache2.conf

# Laravel writable directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

# Railway provides PORT automatically
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-8080}/\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT:-8080}>/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]