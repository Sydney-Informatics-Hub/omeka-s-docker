FROM composer:2.9.2 AS composer

FROM php:8.2-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG OMEKA_DIST_URL=https://github.com/Systemik-Solutions/ngc-omeka
ARG INSTALL_DIR=/var/www/html

WORKDIR $INSTALL_DIR

RUN apt update
RUN apt install -y git

RUN git clone $OMEKA_DIST_URL $INSTALL_DIR

RUN composer install

COPY config.json $INSTALL_DIR/config.json



# these bits copied from the other Omeka docker

COPY docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT [ "/docker-entrypoint.sh" ]

# Start the Apache service
CMD ["apache2-foreground"]

