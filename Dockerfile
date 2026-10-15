FROM php:8.2-apache

# Instalar extensiones necesarias (como pdo_mysql para tu base de datos)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache para las URLs amigables (.htaccess)
RUN a2enmod rewrite

# Copiar los archivos del proyecto al servidor web de Apache
COPY . /var/www/html/

# Ajustar permisos de la carpeta
RUN chown -R www-data:www-data /var/www/html