# ============================================
# MyMember - PHP Backend (CodeIgniter 4)
# ============================================
FROM php:8.3-apache

# Install PHP extensions yang dibutuhkan CI4
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo_mysql zip intl \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files dulu (agar layer cache optimal)
COPY composer.json composer.lock* ./

# Install dependencies PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy seluruh kode aplikasi
COPY . .

# Set permissions untuk folder writable CI4
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Konfigurasi Apache: DocumentRoot ke folder public CI4
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# AllowOverride All untuk .htaccess CI4
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/ci4.conf \
    && a2enconf ci4

EXPOSE 80

CMD ["apache2-foreground"]
