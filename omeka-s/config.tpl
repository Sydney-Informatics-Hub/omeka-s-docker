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
  "title": "My Omeka S Site",
  "timezone": "Australia/Sydney",
  "site": {
    "title": "My Omeka S Site",
    "slug": "my-omeka-s-site",
    "summary": "A brief summary of my Omeka S site.",
    "theme": "default"
  }
}
