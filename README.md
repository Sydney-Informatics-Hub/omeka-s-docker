# Omeka-S Docker

Docker and docker compose scaffolding for deploying the Curated Collections
Omeka S distribution

## Installation

There are two docker compose files. `docker-compose-dev.yml` is for
getting the stack running locally, and `docker-compose.yml` is for running
a prod or uat server on an actual domain.

Both versions use Caddy to do TLS termination, but the dev version uses
a self-signed certificate to put the server on https://omeka-s.localhost.

Because it uses a self-signed cert, your browser will complain and ask
you to make an exception.

### Environment variables

The following environment variables are used by both versions of the
docker compose file:

    DB_DATABASE
    DB_USER
    DB_PASSWORD
    DB_ROOT_PASSWORD
    OMEKA_ADMIN_USER
    OMEKA_ADMIN_EMAIL
    OMEKA_ADMIN_PASSWORD

Set the values you want in a `.env` file or pass them through as flags to
docker compose.

Once the environment variables are set, run

```

> docker compose -f docker-compose-dev.yml build
> docker compose -f docker-compose-dev.yml up

```

If running the dev version, you should be able to visit the server's
public site on `https://omeka-s.localhost` and the admin interface at `https://omeka-s.localhost`

### Live TLS

The prod version also requires

    OMEKA_DOMAIN_NAME
    OMEKA_TLS_EMAIL

The first of these is your server's fully qualified domain name, the
second is the email address used in the Let's Encrypt challenge process,
and should probably be yours

Note that the prod version will only work if the server you are installing
it on already has OMEKA_DOMAIN_NAME pointing to it in DNS, and that the
TLS keys are installed by Caddy on the first page load, not at the Docker
build or run stage - so the first load can be a bit slow. You should also
avoid rerunning this process too often or you'll get rate limited by
Let's Encrypt. Caddy keeps the TLS keys in its own Docker volume,
`caddy-data`, and will do updates automatically.

Building and running is the same as for dev:

```

> docker compose -f docker-compose.yml build
> docker compose -f docker-compose.yml up

```
