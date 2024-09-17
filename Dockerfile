# Gunakan PHP-FPM 8.1 sebagai base image
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip
    # Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libicu-dev \  # Tambahkan library ICU yang diperlukan oleh ext-intl
    curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip intl  # Install ext-intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy source code ke dalam container
WORKDIR /var/www/html
COPY . .

# Install dependencies CodeIgniter
RUN composer install

# Set permission untuk writable folder
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Copy konfigurasi Nginx ke dalam container
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf

# Expose port 80 untuk HTTP
EXPOSE 80

# Jalankan Nginx dan PHP-FPM
CMD ["/bin/bash", "-c", "service nginx start && php-fpm"]