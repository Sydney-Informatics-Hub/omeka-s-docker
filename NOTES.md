# NOTES

Crap first version:

Run the install script as part of the docker-entrypoint, because it
needs the database container to be running.

Later we need a better way to do this which lets us build a complete
Omeka stack image which can then connect to and optionally set up the
database
