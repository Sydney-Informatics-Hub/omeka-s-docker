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

There are three docker-compose files:

- docker-compose-dev.yml is for building and running the stack on your laptop, if it's a Mac
- docker-compose-linux.yml is for building the images to be pushed to the Nectar container registry
- docker-compose.yml is the basis for the template used to deploy on Nectar

The main difference with the production docker-compose.yml is that it
pulls the omeka s app and the database from the Nectar container registry,
and it does full TLS termination with Caddy. The dev version uses a
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
complain/warn that you're doing something scary and ask you to make
an exception - this is fine.

## Passwords and other secrets

The dev docker-compose files expect the following secrets:

    secrets/mariadb_password.txt
    secrets/mariadb_root_password.txt
    secrets/omeka_admin_email.txt
    secrets/omeka_admin_password.txt
    secrets/omeka_admin_user.txt
    secrets/omeka_project_title.txt
    secrets/omeka_site_slug.txt
    secrets/omeka_site_title.txt

The docker-compose-build file expects build versions:

    secrets/build/mariadb_password.txt
    secrets/build/mariadb_root_passsword.txt
    secrets/build/omeka_admin_password.txt
    secrets/build/omeka_admin_user.txt
    secrets/build/omeka_project_title.txt
    secrets/build/omeka_site_slug.txt
    secrets/build/omeka_site_title.txt

The secrets should not have a newline - you can create them using echo with the -n flag like:

    echo -n "rand0mstuff" > mariadb_password.txt

### Production deployment

Production deployment is now done with Terraform, which now sets up the
networking and DNS record, mints random passwords for the database and
admin user, and uses cloud-init to put the docker-compose, Caddyfile and
secrets on the new server. See https://github.com/Sydney-Informatics-Hub/curated-collections-terraform  for more details.

