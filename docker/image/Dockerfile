FROM php:8.2-fpm-bullseye

# Suppress Node update notifications
ENV NO_UPDATE_NOTIFIER=true

ADD setup/ /setup/
RUN /setup/app-docker-setup.sh

WORKDIR /var/www
