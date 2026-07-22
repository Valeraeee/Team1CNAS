FROM mysql:latest
COPY *.sql /docker-entrypoint-initdb.d/
ENV MYSQL_DATABASE mydb
ENV MYSQL_ROOT_PASSWORD rootpass
EXPOSE 3306
