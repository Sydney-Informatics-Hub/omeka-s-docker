#!/bin/bash
set -ex

cd /var/www/html

# transfer Docker secrets to environment

export MARIADB_DATABASE=$(</run/secrets/db_database)
export MARIADB_USER=$(</run/secrets/db_user)
export MARIADB_PASSWORD=$(</run/secrets/db_password)
export OMEKA_ADMIN_USER=$(</run/secrets/omeka_admin_user)
export OMEKA_ADMIN_EMAIL=$(</run/secrets/omeka_admin_email)
export OMEKA_ADMIN_PASSWORD=$(</run/secrets/omeka_admin_password)

 envsubst < /var/www/html/config/config.tpl > /var/www/html/config/config.json

if [ ! -d /var/www/html/public ]; then

     php console install -y
     chown -R www-data:www-data /var/www/html
fi



exec "$@"
