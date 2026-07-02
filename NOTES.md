# NOTES

## June-July - secret injection

This is a bit complicated, but here's what's happening on this branch:

1. Only using Docker secrets for things which change (not the database
user or db, just passwords and the admin account details)

2. dev and prod docker compose now keep secrets in files, not environment,
because that's how we'll be injecting them with cloud-inint on Nectar

3. The pre-build uses its own db password in the environment, and this is
ok, because when we deploy we can use a new one-off one

BUT the php Docker image can't get secrets from BLAH_FILE environment
variables, so now the docker-compose reads the files with cat into
variables and runs envsubst on config.json and database.ini

It doesn't need to do both - this is messy, the Systemik install script
reads values from config.json and uses them to write database.ini. My
deployment rewrites both of them with *this instance's* database password.

I still need to make the prod docker compose do this, and get the secrets
into that using Terraform

What's also left:

- need to check that the root database password is being reset with
the new value, as well as the regular one. This doesn't require fiddling
with Omeka, just have to test it

- The admin account is still being created at build and written into the
database initialisation file. I need to sort out how to update it, or
replace it with a new value, at deployment time.

## July 2

Claude wrote a cli-tool to reset the admin user - testing this now





## First version:

Run the install script as part of the docker-entrypoint, because it
needs the database container to be running.

Later we need a better way to do this which lets us build a complete
Omeka stack image which can then connect to and optionally set up the
database

## Isolated Sites

I can install this but site editor users get a 403 error when trying
to add items.

## Catching up in January

The docker branch installs modules at build time rather than via
the docker_entrypoint.sh but doesn't create an initial user or
add the resource templates.

The right way to do this is with a /docker-entrypoint-initdb.d bind mount
with an SQL file which the mariadb container will use to initialise
the database.

This is ok for the resource templates, but I'm not sure about the
initial user


## Catching up in March

I think I need a two-pass build:

1. docker compose with a special entrypoint that
   - does the full PHP install
   - dumps out the database

2. docker build which injects the database dump and installs modules

The admin user details should be done the right way with a secret.
For now I'm going to concentrate on getting the 1/2 right with a
pre-baked admin user

ChatGPT suggests the following:

services:
  init:
    build: .
    depends_on:
      - db
    command: >
      sh -c "
        ./wait-for-db.sh &&
        php application/scripts/install.php &&
        touch /done
      "

##

What has to happen at build time:

- install Omeka S
- run database and Omeka
- set up a build-time admin user
- Install modules and templates
- dump database SQL
- push image to registry

What has to happen at init

- pull image
- init with database SQL
- inject new password for admin user (before or after SQL init)?
- spin up image

