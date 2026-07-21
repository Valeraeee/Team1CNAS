FROM php:latest AS build
WORKDIR /var/www/html
COPY *.php .
RUN find . -name "*.php" -print0 | xargs -0 -n1 php -l

FROM debian:bookworm-slim
WORKDIR /var/www/html
COPY --from=build /var/www/html .
RUN apt update -y \
    && apt install -y php apache2 libapache2-mod-php
EXPOSE 80
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
