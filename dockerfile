FROM php:8.1-apache

# Copiar archivos desde el contexto actual al contenedor
COPY ./public /var/www/html
COPY ./admin /var/www/html/admin
COPY ./includes /var/www/html/includes
COPY ./assets /var/www/html/assets
COPY . /var/www/html

# Opcional: si tienes controllers
COPY ./controllers /var/www/html/controllers

# Cambiar permisos para que Apache pueda leer los archivos
    
RUN chmod -R 755 /var/www/html && chown -R www-data:www-data /var/www/html


# Habilitar mod_rewrite (si piensas usar .htaccess o URLs amigables)
RUN a2enmod rewrite

COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf
