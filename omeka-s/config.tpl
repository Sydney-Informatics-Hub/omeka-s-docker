{
  "db": {
    "host": "${MARIADB_HOST}",
    "port": 3306,
    "username": "${MARIADB_USER}",
    "password": "${MARIADB_PASSWORD}",
    "database": "${MARIADB_DATABASE}"
  },
  "apache_user": "www-data",
  "admin": {
    "name": "${OMEKA_ADMIN_USER}",
    "email": "${OMEKA_ADMIN_EMAIL}",
    "password": "${OMEKA_ADMIN_PASSWORD}"
  },
  "title": "Omeka S Build",
  "timezone": "Australia/Sydney",
  "site": {
    "title": "Temp Site",
    "slug": "${OMEKA_SITE_SLUG}",
    "summary": "",
    "theme": "default"
  }
}
