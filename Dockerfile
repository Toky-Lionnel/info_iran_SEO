FROM php:8.2-apache

RUN a2enmod rewrite headers expires deflate

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
