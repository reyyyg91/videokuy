FROM php:8.2-cli

WORKDIR /app

# Install dependency system
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP extension
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Upload size config
RUN echo "upload_max_filesize=200M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=200M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/uploads.ini

# Default ENV
ENV APP_DEBUG=false \
    PORT=8080

# Copy project
COPY . /app

# Install composer package
RUN composer install

# Folder upload
RUN mkdir -p /app/thumbnail /app/video && \
    chmod -R 775 /app/thumbnail /app/video

# Railway port
EXPOSE 8080

# Run server
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /app"]