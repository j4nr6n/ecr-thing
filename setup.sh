#!/bin/sh

getEnv() {
    if [ -f '.env.local' ]; then
        . ./.env.local
    fi

    if [ -z ${APP_ENV+x} ]; then
        . ./.env
    fi

    echo "${APP_ENV}"
}

env=$(getEnv)

echo "Setting up project in \"${env}\" mode."
case "${env}" in
    dev)
        composer install
        yarn install
        bin/console doctrine:migrations:status
    ;;
    test)
        composer install
    ;;
    prod)
        composer install --no-dev --prefer-dist --classmap-authoritative
        yarn install && yarn build
        bin/console cache:clear
        bin/console cache:warm
        bin/console doctrine:migrations:status
    ;;
esac
