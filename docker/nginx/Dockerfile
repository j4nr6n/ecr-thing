ARG ASSET_IMAGE=ecr-thing-app:local

FROM ${ASSET_IMAGE} AS assets

FROM nginx:alpine

COPY docker/nginx/app.conf /etc/nginx/conf.d/default.conf

COPY --from=assets /var/www/public /var/www/public
