FROM php:8.2-apache
WORKDIR /var/www/html
COPY *.php .
RUN apt-get update && apt-get upgrade -y && apt-get clean \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/* \
    # Iterates through each php file in the workdir then checks its syntax. Doesn't help with security
    && find . -name "*.php" -print0 | xargs -0 -n1 php -l \
    # Creates a user and usergroup so that root isn't default
    && groupadd --gid 1234 user \
    && useradd --uid 1234 --gid 1234 user
# Logs into new user
USER user
EXPOSE 80
# Auto start Apache on container start
CMD ["/usr/sbin/apache2ctl","-D","FOREGROUND"]
