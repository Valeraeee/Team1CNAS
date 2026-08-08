FROM php:8.2-apache
WORKDIR /var/www/html
COPY *.php .
RUN find . -name "*.php" -print0 | xargs -0 -n1 php -l
RUN docker-php-ext-install mysqli
RUN groupadd --gid 1234 user \
    && useradd --uid 1234 --gid 1234 user
USER user
EXPOSE 8080
CMD ["/usr/sbin/apache2ctl","-D","FOREGROUND"]
