FROM mysql:26.7.0
COPY *.sql /docker-entrypoint-initdb.d/
RUN microdnf update && microdnf upgrade -y && microdnf clean all
EXPOSE 3306
