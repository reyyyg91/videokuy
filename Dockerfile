FROM php:8.2-apache

# Copy all files to the server
COPY . /var/www/html/

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Create upload directory with permissions
RUN mkdir -p /var/www/html/upload && chmod -R 777 /var/www/html/upload

# Fix MPM: properly disable event, enable prefork
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Configure Apache to listen on Railway's PORT (default 80 as fallback)
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:${PORT:-80}/' /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT:-80}

CMD ["apache2-foreground"]
