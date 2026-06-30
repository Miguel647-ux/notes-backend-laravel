FROM php:8.4-apache

# 1. Installer les extensions PHP indispensables pour Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd

# 2. Activer le module de réécriture d'URL Apache (indispensable pour les routes Laravel)
RUN a2enmod rewrite

# 3. Changer le document root d'Apache pour pointer sur le dossier /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf.d/

# 4. Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Définir le dossier de travail et copier le code
WORKDIR /var/www/html
COPY . /var/www/html

# 6. Installer les paquets sans les outils de dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 7. Donner les permissions d'accès à Apache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80