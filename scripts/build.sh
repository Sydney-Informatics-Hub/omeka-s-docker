#!/usr/bin/env bash

set -euo pipefail


# clean up any old build images...
docker compose -f docker-compose-build.yml down \
	--volumes

# ...and any old php assets



docker compose -f docker-compose-build.yml up \
	--build \
	--abort-on-container-exit \

