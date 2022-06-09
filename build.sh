#!/bin/sh

case "${@}" in
    'dev'|'')
        printf "Building Development Image...\n============================"
        docker build -t ecr-thing-app:local-dev \
            --build-arg APP_ENV=dev \
            --build-arg APP_DEBUG=1 \
            .
        docker build -t ecr-thing-web:local-dev \
            --build-arg ASSET_IMAGE=ecr-thing-app:local-dev \
            -f docker/nginx/Dockerfile \
            .

        ;;
    'prod')
        printf "Building Production Image...\n============================"
        docker build -t ecr-thing-app:local .
        docker build -t ecr-thing-web:local \
            -f docker/nginx/Dockerfile \
            .

        ;;
    *)
        printf "Unknown build environment: %s\n" "${@}"
        ;;
esac
