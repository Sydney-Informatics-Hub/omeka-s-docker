#!/bin/bash
#
# This is the pre-build entrypoint which installs modules and
# resource templates

set -ex pipefail

cd /var/www/html

# build time values, which will get overwritten at deployment
# the build values of OMEKA_ADMIN_EMAIL and OMEKA_SITE_SLUG are
# used at deployment to look up the user and site to replace

export MARIADB_PASSWORD=$(</run/secrets/mariadb_build_password)
export OMEKA_ADMIN_USER=$(</run/secrets/omeka_build_admin_user)
export OMEKA_ADMIN_EMAIL=$(</run/secrets/omeka_build_admin_email)
export OMEKA_ADMIN_PASSWORD=$(</run/secrets/omeka_build_admin_password)
export OMEKA_SITE_SLUG=$(</run/secrets/omeka_build_site_slug)

envsubst < /var/www/html/config/config.tpl > /var/www/html/config/config.json


php console install -y

# dump the database so that it can be picked up by the prod docker

mariadb-dump --host $MARIADB_HOST --user $MARIADB_USER -p$MARIADB_PASSWORD --all-databases > /db-init/init-db.sql

# copy the complete /var/www/html so that it can also be included
# in the production docker, cleaning out /php-init/ first

rm -rf /php-init/*

cp -r /var/www/html/* /php-init/

