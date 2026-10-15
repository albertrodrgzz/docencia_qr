<<<<<<< Updated upstream
FROM php:8.2-apache

# Instalar extensiones necesarias (como pdo_mysql para tu base de datos)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache para las URLs amigables (.htaccess)
RUN a2enmod rewrite

# Copiar los archivos del proyecto al servidor web de Apache
COPY . /var/www/html/

# Ajustar permisos de la carpeta
RUN chown -R www-data:www-data /var/www/html
=======
FROM php:8.3-apache

# Actualizar e instalar dependencias del sistema requeridas para GD, ZIP y MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar módulos necesarios de Apache
RUN a2enmod rewrite headers expires deflate

# Permitir que .htaccess gestione el enrutamiento (AllowOverride All)
RUN printf '<Directory /var/www/html>\n\tOptions -Indexes +FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' > /etc/apache2/conf-available/kredentia.conf \
    && a2enconf kredentia

# Adaptar Apache para escuchar en el puerto dinámico de Render ($PORT) o 80 por defecto
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/g' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/g' /etc/apache2/sites-available/000-default.conf

# Copiar el código fuente de la aplicación
COPY . /var/www/html/

# Asegurar directorios de almacenamiento y permisos de usuario web
RUN mkdir -p /var/www/html/almacenamiento/logs \
             /var/www/html/almacenamiento/qrs \
             /var/www/html/almacenamiento/exportaciones \
             /var/www/html/almacenamiento/respaldos_bd \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/almacenamiento

EXPOSE 80

CMD ["apache2-foreground"]
>>>>>>> Stashed changes
