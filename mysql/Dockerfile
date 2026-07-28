# =============================================================================
# MySQL Dockerfile – John (SIGMA)
# Pins to a specific minor version for reproducible builds.
# Init SQL is copied in so the schema is created automatically on first boot.
# =============================================================================
FROM mysql:8.0.36

# Metadata
LABEL maintainer="Team1CNAS"
LABEL description="MySQL 8 database for CNAS PHP webapp"

# Set sane defaults – these are overridden at runtime via K8s Secrets
ENV MYSQL_DATABASE=mydb \
    MYSQL_ROOT_PASSWORD=changeme

# Copy schema init script; MySQL executes everything in this directory
# alphabetically on first startup when the data directory is empty
COPY users.sql /docker-entrypoint-initdb.d/01-schema.sql

# Use a non-default port to reduce automated scanning noise (optional, keep 3306
# if the rest of the cluster config targets 3306 – which it does, so we keep it)
EXPOSE 3306

# Healthcheck so Docker/K8s knows when MySQL is actually ready to accept connections
HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=5 \
  CMD mysqladmin ping -h 127.0.0.1 -u root --password="${MYSQL_ROOT_PASSWORD}" || exit 1
