#!/usr/bin/env sh

# This script can be executed in Docker container to update composer dependencies and composer.lock for specified
# version of PHP.

PHP_VERSION=$1 # 5.6, 7.2, 7.4 etc.
LATEST_PHP_VERSION='7.4'
BASE_PATH="$(dirname "$0")"
COMPOSER_LOCK_TO_UPDATE="${BASE_PATH}/composer-${PHP_VERSION}.lock"

# change directory to directory containing composer.json
cd "${BASE_PATH}/../.." # || exit # todo test for shellcheck

# remove composer.lock because it probably created for other version of PHP
rm composer.lock
composer update
mv --force composer.lock "${COMPOSER_LOCK_TO_UPDATE}"
cp "${BASE_PATH}/composer-${LATEST_PHP_VERSION}.lock" composer.lock
chown 1000:1000 composer.lock "${COMPOSER_LOCK_TO_UPDATE}"
chmod 664 composer.lock "${COMPOSER_LOCK_TO_UPDATE}"
