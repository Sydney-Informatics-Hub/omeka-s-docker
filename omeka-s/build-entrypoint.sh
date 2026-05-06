#!/bin/bash
#
# This is the pre-build entrypoint which installs modules and
# resource templates

set -ex pipefail

cd /var/www/html


export MARIADB_DATABASE=$(</run/secrets/db_database)
export MARIADB_USER=$(</run/secrets/db_user)
export MARIADB_PASSWORD=$(</run/secrets/db_password)
export OMEKA_ADMIN_USER=$(</run/secrets/omeka_admin_user)
export OMEKA_ADMIN_EMAIL=$(</run/secrets/omeka_admin_email)
export OMEKA_ADMIN_PASSWORD=$(</run/secrets/omeka_admin_password)

envsubst < /var/www/html/config/config.tpl > /var/www/html/config/config.json

echo "test to see if this is being run"

php console install -y
chown -R www-data:www-data /var/www/html

# dump the database so that it can be picked up by the prod docker

mariadb-dump --host $MARIADB_HOST --user $MARIADB_USER -p$MARIADB_PASSWORD --all-databases > /output/init-db.sql

