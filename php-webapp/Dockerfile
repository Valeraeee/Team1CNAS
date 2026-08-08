FROM php:8.2-apache
WORKDIR /var/www/html
COPY *.php .
# Iterates through each php file in the workdir
# then checks its syntax. Doesn't help with security
RUN find . -name "*.php" -print0 | xargs -0 -n1 php -l
# Below just runs security updates in case
RUN apt-get update && apt-get upgrade -y \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*
# Creates a new group and user then logs into it
RUN groupadd --gid 1234 user \
    && useradd --uid 1234 --gid 1234 user
USER user
# EXPOSE does nothing, just for documentation
EXPOSE 80
# Auto start Apache on container start
CMD ["/usr/sbin/apache2ctl","-D","FOREGROUND"]
