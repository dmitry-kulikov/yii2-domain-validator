# These instructions allow to create Docker image to run tests for various versions of PHP.

ARG PHP_VERSION=5.6
FROM php:${PHP_VERSION}-cli

# install PHP extensions intl and xdebug
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions --cleanup intl xdebug

COPY . /usr/src/yii2-domain-validator
WORKDIR /usr/src/yii2-domain-validator
CMD ["php", "./vendor/bin/phpunit"]
