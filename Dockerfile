FROM php:8.1-apache

# Install PostgreSQL extensions for PDO
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides for the document root
RUN printf '%s\n' '<Directory "/var/www/html">' '    AllowOverride All' '</Directory>' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

WORKDIR /var/www/html
EXPOSE 80
