#!/bin/bash
#
# This is the deployment entrypoint which sets the instance-specific
# database credentials, resets the local admin password if it
# hasn't already been created, and configures the OIDC connector

set -ex pipefail

cd /var/www/html

# deploy values

export MARIADB_PASSWORD=$(</run/secrets/mariadb_password)
export OMEKA_ADMIN_USER=$(</run/secrets/omeka_admin_user)
export OMEKA_ADMIN_EMAIL=$(</run/secrets/omeka_admin_email)
export OMEKA_ADMIN_PASSWORD=$(</run/secrets/omeka_admin_password)
export OMEKA_BUILD_ADMIN_EMAIL=$(</run/secrets/omeka_build_admin_email)
export OMEKA_PROJECT_TITLE=$(</run/secrets/omeka_project_title)
export OMEKA_SITE_TITLE=$(</run/secrets/omeka_site_title)
export OMEKA_SITE_SLUG=$(</run/secrets/omeka_site_slug)
export OMEKA_BUILD_SITE_SLUG=$(</run/secrets/omeka_build_site_slug)

export OIDC_BASE_URL=$(</run/secrets/oidc_base_url)
export OIDC_DISCOVERY_URL=$(</run/secrets/oidc_discovery_url)
export OIDC_CLIENT_ID=$(</run/secrets/oidc_client_id)
export OIDC_CLIENT_SECRET=$(</run/secrets/oidc_client_secret)
export OIDC_ACCESS_CLAIM=$(</run/secrets/oidc_access_claim)
export OIDC_ACCESS_VALUE=$(</run/secrets/oidc_access_value)

# note: I don't think config.json is used after installation so
# it should probably be cleaned up
envsubst < /var/www/html/config/config.tpl > /var/www/html/config/config.json
envsubst < /var/www/html/public/config/database.ini.tpl > /var/www/html/public/config/database.ini

cd /var/www/html/public

# this won't do anything if the site admin user email exists

php reset-admin.php \
    --find-by-email="$OMEKA_BUILD_ADMIN_EMAIL" \
    --name="$OMEKA_ADMIN_USER" \
    --email="$OMEKA_ADMIN_EMAIL" \
    --password="$OMEKA_ADMIN_PASSWORD"

php reset-settings.php \
    --installation-title="$OMEKA_PROJECT_TITLE" \
    --administrator-email="$OMEKA_ADMIN_EMAIL" \
    --site-title="$OMEKA_SITE_TITLE" \
    --site-slug="$OMEKA_SITE_SLUG" \
    --find-site-by-slug="$OMEKA_BUILD_SITE_SLUG"

php set-oidc-config.php \
    --base-url="OIDC_BASE_URL" \
    --idp-discovery-url="$OIDC_DISCOVERY_URL" \
    --client-id="$OIDC_CLIENT_ID" \
    --client-secret="$OIDC_CLIENT_SECRET"\
    --access-guard-claim="$OIDC_ACCESS_CLAIM" \
    --access-guard-value="$OIDC_ACCESS_VALUE"

exec docker-php-entrypoint apache2-foreground

