FROM php:8.4-apache

# 1. Installer les dépendances système requises
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Activer le module de réécriture d'Apache
RUN a2enmod rewrite

# 3. Modifier le point d'entrée d'Apache proprement pour Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 4. Installer Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5. Définir le dossier de travail et copier le projet
WORKDIR /var/www/html
COPY . .

# 6. Installer les dépendances en ignorant les contraintes de plateforme de production
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 7. Configurer les droits d'accès
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80