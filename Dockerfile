FROM php:8.2-cli

WORKDIR /app

# Install MySQL extensions used by koneksi.php
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application code
COPY . /app

# Ensure runtime directories exist for uploaded files
RUN mkdir -p /app/thumbnail /app/video && chmod -R 775 /app/thumbnail /app/video

# Railway injects PORT at runtime
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /app /app/index.php"]
