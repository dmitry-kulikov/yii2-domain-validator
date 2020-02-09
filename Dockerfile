# examples of allowed values: 5.6-cli, 5.6-cli-alpine, 7.2-cli, 7.2-cli-alpine
# and other tags from https://hub.docker.com/_/php
ARG PHP_VERSION=5.6-cli

FROM php:${PHP_VERSION}
ARG COMPOSER_SUFFIX
ARG GITHUB_OAUTH_TOKEN

# install packages
RUN apt-get update \
    && apt-get install -y \
        git `# for composer` \
        unzip `# for composer`

# install PHP extensions
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions \
    /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions \
    && sync \
    && install-php-extensions \
        intl `# for app` \
        pcntl `# for tests` \
        xdebug `# for tests`

# install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer config -g github-oauth.github.com ${GITHUB_OAUTH_TOKEN} \
    && composer global require "fxp/composer-asset-plugin:^1.4.6"

WORKDIR /usr/src/yii2-domain-validator

COPY composer-${COMPOSER_SUFFIX}.* ./
RUN COMPOSER=composer-${COMPOSER_SUFFIX}.json composer install
