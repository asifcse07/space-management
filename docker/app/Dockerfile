FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libicu-dev \
    libonig-dev libxml2-dev libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql intl zip gd

RUN a2enmod rewrite headers

ARG APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APACHE_DOCUMENT_ROOT=${APACHE_DOCUMENT_ROOT}
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT%/public}!g' /etc/apache2/apache2.conf

COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/conf.d/php-custom.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html
