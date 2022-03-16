FROM php:8.1-fpm-alpine

COPY . /var/www
WORKDIR /var/www

EXPOSE 8080

ENTRYPOINT php ./bin/start.php