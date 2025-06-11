FROM php:8.0-apache
COPY . /var/www/html/
RUN docker-php-ext-install pdo pdo_sqlite
RUN chmod 777 /var/www/html/log.txt
EXPOSE 80
