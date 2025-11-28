#!/bin/bash
set -ex

cd /var/www/html

# Secrets from docker secret mounts

export MARIADB_PASSWORD=$(</run/secrets/db_password)
export OMEKA_ADMIN_PASSWORD=$(</run/secrets/omeka_admin_password)

envsubst < /var/www/html/config/config.tpl > /var/www/html/config/config.json

if [ ! -d /var/www/html/public ]; then

    php console install -y
    chown -R www-data:www-data /var/www/html
fi



exec "$@"
