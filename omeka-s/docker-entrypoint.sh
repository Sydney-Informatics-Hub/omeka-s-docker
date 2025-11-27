#!/bin/bash
set -ex

cd /var/www/html

if [ ! -d /var/www/html/public ]; then
    php console install -y
    chown -R www-data:www-data /var/www/html
fi


exec "$@"
