#!/usr/bin/env bash

set -o errexit

export DEBIAN_FRONTEND=noninteractive

PHP_XDEBUG_VERSION=3.2.0

PARENT_DIR="$(dirname "$(readlink -f "$0")")"

apt_install_tools() {
  apt-get -qq install -y --no-install-recommends \
    git \
    curl \
    procps \
    psmisc \
    sudo \
    nano \
    rsync \
    iproute2 \
    iputils-ping \
    dnsutils
}

apt_install_php_runtime() {
  apt-get -qq install -y --no-install-recommends \
    zip \
    unzip \
    zlib1g-dev \
    libzip-dev
}

setup_php_configs() {
  mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
  ln -s /usr/local/bin/php /usr/bin/php

  cp "$PARENT_DIR/10-app.ini" "$PHP_INI_DIR/conf.d/"
}

setup_php_extensions() {
  docker-php-ext-install "-j$(nproc)" pdo_mysql
  docker-php-ext-install "-j$(nproc)" zip

  pecl install "xdebug-$PHP_XDEBUG_VERSION"
}

apt_clear_cache() {
  apt-get -qq clean
  rm -rf /var/lib/apt/lists/*
  truncate -s 0 /var/log/*log
}

apt-get -qq update
apt_install_tools
apt_install_php_runtime
setup_php_configs
setup_php_extensions
apt_clear_cache

# Remove this script
rm "$(readlink -f "$0")"
