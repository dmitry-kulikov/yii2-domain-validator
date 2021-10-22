# syntax=docker/dockerfile:1.3.0-labs

# PHP version
# examples of allowed values: 5.6-cli, 5.6-cli-alpine, 7.4-cli, 7.4-cli-alpine
# and other tags from https://hub.docker.com/_/php
ARG PHP_VERSION=5.6-cli

########################################################################################################################
FROM php:${PHP_VERSION} AS default

WORKDIR /usr/src/yii2-domain-validator

# install PHP extensions
RUN curl --silent --show-error --location --output /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/download/1.2.60/install-php-extensions \
    && chmod a+x /usr/local/bin/install-php-extensions \
    && sync \
    && install-php-extensions \
        intl `# for app` \
        pcntl `# for tests` \
        xdebug `# for tests`

# install the latest stable Composer 1.x version
RUN curl --silent --show-error --location https://getcomposer.org/installer | php -- --1 \
    && mv composer.phar /usr/local/bin/composer

COPY composer.json ./

########################################################################################################################
FROM default AS alpine

# install system packages
RUN apk update \
    && apk add \
        git `# for Composer and developers` \
        nano `# for developers` \
        unzip `# for Composer`

# install dependencies using Composer
RUN --mount=type=cache,id=composer,target=/root/.composer/cache,sharing=locked \
    composer global require --optimize-autoloader 'fxp/composer-asset-plugin:^1.4.6' \
    && composer update \
    && composer clear-cache

########################################################################################################################
FROM default AS debian

# install system packages
RUN apt-get update \
    && apt-get --assume-yes --no-install-recommends install \
        gnupg2 \
    && apt-key update \
    && apt-get update \
    && apt-get --assume-yes --no-install-recommends install \
        git `# for Composer and developers` \
        nano `# for developers` \
        unzip `# for Composer` \

    # clean up
    && rm --force --recursive /var/lib/apt/lists/* /tmp/* /var/tmp/*

# install dependencies using Composer
RUN --mount=type=cache,id=composer,target=/root/.composer/cache,sharing=locked \
    composer global require --optimize-autoloader 'fxp/composer-asset-plugin:^1.4.6' \
    && composer update \
    && composer clear-cache

########################################################################################################################
FROM debian AS debian-runkit

# install runkit extension
RUN pecl install \
    runkit `# for tests`

########################################################################################################################
FROM debian AS debian-runkit7

# install runkit7 extension
RUN pecl install \
    runkit7-4.0.0a3 `# for tests`
