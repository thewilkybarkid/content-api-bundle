#!/usr/bin/env bash
set -e

name=$1
health=$2
options=$3

docker run --detach --name="${name}" --health-cmd="${health}" ${options} > /dev/null

while STATUS=$(docker inspect --format "{{.State.Health.Status }}" ${name}); [[ ${STATUS} != "healthy" ]]; do
    if [[ ${STATUS} == "unhealthy" ]]; then
        printf "%s is unhealthy\n" "$name"
        exit 1
    fi
    printf .
    sleep 1
done
printf "\n%s is healthy\n" "$name"
