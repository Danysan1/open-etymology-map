#!/bin/bash

cd $(dirname "$0")
/usr/bin/git fetch --recurse-submodules
/usr/bin/git pull --recurse-submodules

# https://docs.docker.com/compose/profiles/#enable-profiles
cd osm-wikidata-map-framework-etymology
export COMPOSE_PROFILES=prod,promtail
/usr/local/bin/docker-compose pull
if [ '--build' == "$1" ]; then
    /usr/local/bin/docker-compose build
    /usr/local/bin/docker-compose push
fi
/usr/local/bin/docker-compose up --detach --always-recreate-deps
/usr/bin/docker image prune -af
watch /usr/bin/docker ps
