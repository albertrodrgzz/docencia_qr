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

# Instalar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código fuente de la aplicación
COPY . /var/www/html/

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias de producción automáticamente
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Asegurar directorios de almacenamiento y permisos de usuario web
RUN mkdir -p /var/www/html/almacenamiento/logs \
             /var/www/html/almacenamiento/qrs \
             /var/www/html/almacenamiento/exportaciones \
             /var/www/html/almacenamiento/respaldos_bd \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/almacenamiento

EXPOSE 80

# Iniciar Apache adaptando el puerto si Render inyecta la variable $PORT
CMD ["sh", "-c", "if [ -n \"$PORT\" ]; then sed -i \"s/80/$PORT/g\" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf; fi && exec apache2-foreground"]
