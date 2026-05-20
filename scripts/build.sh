#!/usr/bin/env bash

set -euo pipefail


# clean up
docker compose -f docker-compose-build.yml down \
	--volumes

docker compose -f docker-compose-build.yml up \
	--build \
	--abort-on-container-exit

