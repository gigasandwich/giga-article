FROM php:8.1-apache

# Install PostgreSQL extensions for PDO
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache modules: rewrite, compression (deflate/filter), and cache (expires/headers)
RUN a2enmod rewrite deflate filter expires headers

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
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set max upload size
RUN echo "upload_max_filesize=5M" > /usr/local/etc/php/conf.d/uploads.ini \
 && echo "post_max_size=5M" >> /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
