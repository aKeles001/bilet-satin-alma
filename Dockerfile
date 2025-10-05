FROM php:8.2-apache

# Install required dependencies for SQLite PDO
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy all project files
COPY . /var/www/html/

# Set permissions for SQLite database
RUN chown -R www-data:www-data /var/www/html/db

EXPOSE 80
