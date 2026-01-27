# NOTES

First version:

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

