# Omeka-S Docker

Docker and docker compose scaffolding for deploying the Curated Collections
Omeka S distribution

## Installation

Installing the complete distribution has four stages:

- installing the dependencies for Omeka S
- installing Omeka S
- installing the modules in the distribution
- installing the resource templates and vocabularies

This process is complicated by the fact that Omeka S and the database
have to be running for the third and fourth stages. One way to get
around this is to have the last two stages run as part of the Docker
entrypoint, which is how the first version of this worked. This isn't
particularly good practice, and it means that we don't have a
self-contained Docker image we can deploy quickly.

The current version uses a multi-stage build - spinning up a temporary
version of the stack to do the install which requires a running db,
and then dumping the database and web server state out as assets.

The second build stage copies the web server state into a production
Docker image, which has a complete Omeka S web hierachy including the
installed modules.

There are two deployment docker-compose files - one for dev and one for
production. Both of these run the Docker image and will initialise the
database from the SQL dump generated in the first build stage. The
difference between the dev and production docker compose stacks is that
production does full Caddy TLS termination. The dev version uses a
self-signed certificate to run the server on https://omeka-s.localhost/

### The docker compose files 

`docker-compose-build.yml` - the temporary stack for installing and
populating the database and dumping out assets.  Should be run via
`scripts/build.sh` which tidies up any existing build images and invokes
docker compose in the right way so that it exits after the build.

`docker-compose-dev.yml` - should be invoked with --build to do the
second build stage

`docker-compose.yml` - the production version with TLS

A short summary of how to build a dev environment:

```
> ./scripts/build.sh

[ ... the build process ... ]

> docker compose -f docker-compose-dev.yml build

[ ... slightly shorter build process ... ]

> docker compose -f docker-compose-dev.yml up
```

Because the dev compose uses a self-signed cert, your browser will
complain/warn that you're doing something scary  and ask you to make
an exception.

### TODO

This build process still bakes in an admin user and password set by
environment variables. We need to figure out a way to inject a new
set of admin credentions when deploying a database for the first time.

## Environment variables

The following environment variables are used by all versions of the
docker compose file

    DB_DATABASE
    DB_USER
    DB_PASSWORD
    DB_ROOT_PASSWORD
    OMEKA_ADMIN_USER
    OMEKA_ADMIN_EMAIL
    OMEKA_ADMIN_PASSWORD

Set the values you want in a `.env` file or pass them through as flags to
docker compose.

### Live TLS

The prod version also requires

    OMEKA_DOMAIN_NAME
    OMEKA_TLS_EMAIL

The first of these is your server's fully qualified domain name, the
second is the email address used in the Let's Encrypt challenge process,
and should probably be yours.

Note that the prod version will only work if the server you are installing
it on already has OMEKA_DOMAIN_NAME pointing to it in DNS, and that the
TLS keys are installed by Caddy on the first page load, not at the Docker
build or run stage - so the first load can be a bit slow. You should also
avoid rerunning this process too often or you'll get rate limited by
Let's Encrypt. Caddy keeps the TLS keys in its own Docker volume,
`caddy-data`, and will do updates automatically.

Building and running is the same as for dev, assuming that the first
build stage was already run on this machine

```

> docker compose -f docker-compose.yml build
> docker compose -f docker-compose.yml up

```

TODO - for production deployment we should have the prebaked image
on the Nectar container registry
