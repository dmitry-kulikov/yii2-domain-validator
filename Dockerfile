# syntax=docker/dockerfile:1.1.3-experimental

# examples of allowed values: 5.6-cli, 5.6-cli-alpine, 7.2-cli, 7.2-cli-alpine
# and other tags from https://hub.docker.com/_/php
ARG PHP_VERSION=5.6-cli

########################################################################################################################
FROM php:${PHP_VERSION} AS default
ARG COMPOSER_SUFFIX=5.6

# install PHP extensions
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions \
    /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions \
    && sync \
    && install-php-extensions \
        intl `# for app` \
        pcntl `# for tests` \
        xdebug `# for tests`

WORKDIR /usr/src/yii2-domain-validator

# install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && ln -s "$(pwd)/tests/composer/auth.json" /root/.composer/auth.json

########################################################################################################################
FROM default AS alpine

# install packages
RUN apk update \
    && apk add \
        git `# for composer`

# install dependencies using composer
COPY tests/composer/composer-${COMPOSER_SUFFIX}.* ./
RUN --mount=type=bind,target=tests/composer/auth.json,source=tests/composer/auth.json \
    --mount=type=cache,id=composer,target=/root/.composer/cache,sharing=locked \
    composer global require "fxp/composer-asset-plugin:^1.4.6" \
    && COMPOSER=composer-${COMPOSER_SUFFIX}.json composer install

########################################################################################################################
FROM default AS debian

# install packages
RUN apt-get update \
    && apt-get install -y \
        git `# for composer` \
        unzip `# for composer`

# install dependencies using composer
COPY tests/composer/composer-${COMPOSER_SUFFIX}.* ./
RUN --mount=type=bind,target=tests/composer/auth.json,source=tests/composer/auth.json \
    --mount=type=cache,id=composer,target=/root/.composer/cache,sharing=locked \
    composer global require "fxp/composer-asset-plugin:^1.4.6" \
    && COMPOSER=composer-${COMPOSER_SUFFIX}.json composer install

########################################################################################################################
FROM debian AS debian-runkit

# install runkit extension
RUN pecl install \
    runkit `# for tests`

# configure runkit
COPY tests/runkit/runkit.ini /usr/local/etc/php/conf.d/

########################################################################################################################
FROM debian AS debian-runkit7

# install runkit7 extension
RUN pecl install \
    runkit7-3.1.0a1 `# for tests`

# configure runkit7
COPY tests/runkit/runkit7.ini /usr/local/etc/php/conf.d/
