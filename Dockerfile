FROM php:8.2-apache

# Activar módulos de Apache
RUN a2enmod rewrite

# Instalar dependencias necesarias + mysqli
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Copiar archivos del frontend y backend
COPY ./frontend/public/ /var/www/html/
COPY ./frontend/assets/ /var/www/html/assets/
COPY ./backend/admin/ /var/www/html/admin/
COPY ./backend/controllers/ /var/www/html/controllers/
COPY ./backend/includes/ /var/www/html/includes/

# Configuración de Apache
COPY ./master/docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Permisos
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
