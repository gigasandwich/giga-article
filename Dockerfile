FROM php:8.1-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides for the document root
RUN printf '%s\n' '<Directory "/var/www/html">' '    AllowOverride All' '</Directory>' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

# Install dependencies for Composer
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
EXPOSE 80
