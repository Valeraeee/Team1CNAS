FROM mysql:26.7.0
COPY *.sql /docker-entrypoint-initdb.d/
RUN microdnf update && microdnf upgrade -y \
    && microdnf install -y python3-cryptography \
    && rm -rf /var/lib/apt/lists/* \
    && microdnf clean all
EXPOSE 3306
