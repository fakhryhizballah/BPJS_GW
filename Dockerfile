# Menggunakan image PHP 8.1 dengan PHP-FPM
FROM php:8.1-fpm

# Install dependencies tambahan
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    libzip-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy source code ke dalam container
WORKDIR /var/www/html
COPY . .

# Install dependencies CodeIgniter menggunakan Composer
RUN composer install

# Berikan izin untuk direktori writable CodeIgniter
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Copy konfigurasi Nginx ke dalam container
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf

# Expose port 80 untuk HTTP
EXPOSE 80

# Script untuk menjalankan Nginx dan PHP-FPM secara bersamaan
CMD ["/bin/bash", "-c", "service nginx start && php-fpm"]