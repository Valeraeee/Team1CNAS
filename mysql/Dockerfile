FROM mysql:8.0
COPY *.sql /docker-entrypoint-initdb.d/
RUN microdnf update && microdnf upgrade -y \
    && microdnf install -y python3-cryptography \
    && microdnf clean all
EXPOSE 3306
