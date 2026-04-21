# NOTES

Crap first version:

Run the install script as part of the docker-entrypoint, because it
needs the database container to be running.

Later we need a better way to do this which lets us build a complete
Omeka stack image which can then connect to and optionally set up the
database

WHERE I'M UP TO

- forked the Mapping Extensions to introduce a leaflet-sidebar
- packaged it and edited the distribution.json to get the hacked version
- the install is failing when building it

To Do this afternoon -

Try just installing it to the docker container with docker cp so I can iterate faster
