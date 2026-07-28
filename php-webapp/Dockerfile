# =============================================================================
# Stage 1 – Syntax Validation (php-cli)
# Validates every .php file before anything is copied into the final image.
# If any file fails php -l the build aborts here, never producing a bad image.
# =============================================================================
FROM php:8.2-cli AS validate

WORKDIR /validate
COPY *.php ./

# Lint all PHP files; non-zero exit on any syntax error stops the build
RUN find . -name "*.php" -print0 | xargs -0 -n1 php -l

# =============================================================================
# Stage 2 – Dependency / Extension Builder
# Installs mysqli into a throw-away layer so the final image stays small.
# =============================================================================
FROM php:8.2-apache AS builder

WORKDIR /var/www/html

# Install mysqli extension (needed to talk to MySQL)
RUN docker-php-ext-install mysqli \
 && docker-php-ext-enable mysqli

# =============================================================================
# Stage 3 – Production Image
# Only the validated source files and the compiled extension land here.
# =============================================================================
FROM php:8.2-apache AS production

WORKDIR /var/www/html

# Pull in the compiled mysqli extension from the builder stage
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/     /usr/local/etc/php/conf.d/

# Copy only the syntax-validated PHP source from Stage 1
COPY --from=validate /validate/ ./

# Run Apache as a non-root user where possible; www-data is the Apache default
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# Disable directory listing and hide server tokens for basic hardening
RUN echo "ServerTokens Prod\nServerSignature Off" >> /etc/apache2/apache2.conf \
 && echo "Options -Indexes" >> /etc/apache2/conf-available/security.conf

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]
