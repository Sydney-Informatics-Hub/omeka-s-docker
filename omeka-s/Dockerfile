FROM composer:2.9.2 AS composer


FROM php:8.2-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG OMEKA_DIST_URL=https://github.com/Systemik-Solutions/ngc-omeka
ARG INSTALL_DIR=/var/www/html/omeka-s

WORKDIR $INSTALL_DIR

RUN apt update
RUN apt install -y git

RUN git clone $OMEKA_DIST_URL $INSTALL_DIR

RUN composer install

# Note: don't do this stuff, use "secret mounts" https://docs.docker.com/build/building/secrets/
# ARG DB_USER
# ARG DB_PASSWORD
# ARG DB_NAME
# ARG DB_HOST
# ARG DB_PORT

# # Set DB environment variables
# ENV DB_USER=$DB_USER
# ENV DB_PASSWORD=$DB_PASSWORD
# ENV DB_NAME=$DB_NAME
# ENV DB_HOST=$DB_HOST
# ENV DB_PORT=$DB_PORT



