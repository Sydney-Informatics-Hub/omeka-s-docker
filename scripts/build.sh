#!/usr/bin/env bash

set -euo pipefail


# clean up any old build images
docker compose -f docker-compose-build.yml down \
	--volumes

docker compose -f docker-compose-build.yml up \
	--build \
	--abort-on-container-exit \

mv init-db/init-db.sql mariadb/

