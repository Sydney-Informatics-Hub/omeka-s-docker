# Omeka-S Docker

Docker and docker compose scaffolding for deploying the Curated Collections
Omeka S distribution

## Installation

The following environment variables are used by the docker compose script:

    DB_DATABASE
    DB_USER
    DB_PASSWORD
    DB_ROOT_PASSWORD
    OMEKA_ADMIN_USER
    OMEKA_ADMIN_EMAIL
    OMEKA_ADMIN_PASSWORD
    OMEKA_PORT

Set the values you want in a `.env` file or pass them through as flags to
docker compose.

Once the environment variables are set, run

```

> docker compose build
> docker compose up

```

You should be able to visit the server's public site on `http://localhost/public` and the admin interface at `http://localhost/public/admin`
