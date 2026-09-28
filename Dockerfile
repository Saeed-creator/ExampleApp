FROM php:8.3-apache

# --------------------------------------------------
# Install system dependencies and PHP extensions
# --------------------------------------------------
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


# --------------------------------------------------
# Configure Apache
# --------------------------------------------------

# php:8.3-apache should use prefork with mod_php.
# Explicitly make sure no other MPM is enabled.
RUN rm -f \
        /etc/apache2/mods-enabled/mpm_event.load \
        /etc/apache2/mods-enabled/mpm_event.conf \
        /etc/apache2/mods-enabled/mpm_worker.load \
        /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2enmod mpm_prefork \
    && a2enmod rewrite


# --------------------------------------------------
# Install Composer
# --------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# --------------------------------------------------
# Laravel application
# --------------------------------------------------
WORKDIR /var/www/html

COPY . .


# --------------------------------------------------
# Install PHP dependencies
# --------------------------------------------------
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress


# --------------------------------------------------
# Configure Apache DocumentRoot for Laravel
# --------------------------------------------------
RUN sed -i \
    's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf


# --------------------------------------------------
# Allow Laravel .htaccess
# --------------------------------------------------
RUN sed -i \
    '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
    /etc/apache2/apache2.conf


# --------------------------------------------------
# Configure Apache to listen on Railway port 8080
# --------------------------------------------------
RUN sed -i 's/Listen 80/Listen 8080/' \
        /etc/apache2/ports.conf \
    && sed -i \
        's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
        /etc/apache2/sites-available/000-default.conf


# --------------------------------------------------
# Laravel directory permissions
# --------------------------------------------------
RUN chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache \
    && chmod -R 775 \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache


# --------------------------------------------------
# Validate Apache configuration during build
# --------------------------------------------------
RUN apache2ctl configtest


# --------------------------------------------------
# Railway networking
# --------------------------------------------------
EXPOSE 8080


# --------------------------------------------------
# Start Apache
# --------------------------------------------------
CMD ["apache2-foreground"]