# https://registry.hub.docker.com/_/mariadb?tab=description
FROM mariadb:10.7.1

# Defaults
ENV MARIADB_DATABASE=segelflug_db \
    MARIADB_USER=segelflug_db_user

# Copy SQL scripts to mariadb autorun folder
COPY . /docker-entrypoint-initdb.d