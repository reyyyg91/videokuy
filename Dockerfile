FROM php:8.2-apache

# Copy semua file ke server
COPY . /var/www/html/

# Install ekstensi MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod rewrite (kalau pakai .htaccess)
RUN a2enmod rewrite

EXPOSE 80

RUN chmod -R 777 /var/www/html/upload