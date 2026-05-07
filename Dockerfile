FROM php:8.2-cli

WORKDIR /app

# Install ekstensi MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Default ENV config
ENV APP_DEBUG=false \
    MYSQLHOST=localhost \
    MYSQLUSER=root \
    MYSQLPASSWORD= \
    MYSQLDATABASE=raihan232175 \
    MYSQLPORT=3306 \
    PORT=8080

# Copy project
COPY . /app

# Buat folder upload
RUN mkdir -p /app/thumbnail /app/video && \
    chmod -R 775 /app/thumbnail /app/video

# Expose port
EXPOSE 8080

# Run PHP server
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /app"]