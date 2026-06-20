FROM php:8.2-apache

# Habilitar mod_rewrite (necesario para enrutar bonito y proteger rutas)
RUN a2enmod rewrite headers

# Extensiones PHP necesarias: pdo_pgsql para la BD (requiere libpq-dev)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Composer (gestor de dependencias PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar Apache para que sirva desde /public (buena práctica: separar código de docroot)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

EXPOSE 80
