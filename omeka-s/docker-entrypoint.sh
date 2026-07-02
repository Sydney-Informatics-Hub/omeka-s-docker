#!/bin/bash
#
# This is the deployment entrypoint which sets the instance-specific
# database credentials and resets the local admin password if it
# hasn't already been created

set -ex pipefail

cd /var/www/html

# deploy values

export MARIADB_PASSWORD=$(</run/secrets/mariadb_password)
export OMEKA_ADMIN_USER=$(</run/secrets/omeka_admin_user)
export OMEKA_ADMIN_EMAIL=$(</run/secrets/omeka_admin_email)
export OMEKA_ADMIN_PASSWORD=$(</run/secrets/omeka_admin_password)
export OMEKA_ADMIN_TEMP_EMAIL=$(</run/secrets/omeka_admin_temp_email)

# note: I don't think config.json is used after installation so
# it should probably be cleaned up
envsubst < /var/www/html/config/config.tpl > /var/www/html/config/config.json
envsubst < /var/www/html/public/config/database.ini.tpl > /var/www/html/public/config/database.ini

cd /var/www/html/public

# this won't do anything if the admin user email exists

php reset-admin.php \
    --find-by-email=$OMEKA_TEMP_ADMIN_EMAIL \
    --name=$OMEKA_ADMIN_USER \
    --email=$OMEKA_ADMIN_EMAIL \
    --password=$OMEKA_ADMIN_PASSWORD

exec docker-php-entrypoint apache2-foreground

