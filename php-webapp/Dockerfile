FROM php:latest AS build
WORKDIR /var/www/html
COPY *.php .
RUN find . -name "*.php" -print0 | xargs -0 -n1 php -l

FROM php:8.2-apache
WORKDIR /var/www/html
COPY --from=build /var/www/html .
RUN docker-php-ext-install mysqli
EXPOSE 80
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]