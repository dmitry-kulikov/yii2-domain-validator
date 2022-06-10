# yii2-domain-validator

Domain validator for Yii 2.

[![License](https://poser.pugx.org/kdn/yii2-domain-validator/license)](https://packagist.org/packages/kdn/yii2-domain-validator)
[![Latest Stable Version](https://poser.pugx.org/kdn/yii2-domain-validator/v/stable)](https://packagist.org/packages/kdn/yii2-domain-validator)
[![Code Coverage](https://scrutinizer-ci.com/g/dmitry-kulikov/yii2-domain-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dmitry-kulikov/yii2-domain-validator/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dmitry-kulikov/yii2-domain-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dmitry-kulikov/yii2-domain-validator/?branch=master)
[![Code Climate](https://codeclimate.com/github/dmitry-kulikov/yii2-domain-validator/badges/gpa.svg)](https://codeclimate.com/github/dmitry-kulikov/yii2-domain-validator)

## Requirements

- PHP 5.4 or later or HHVM 3;
- Yii framework 2;
- PHP extensions:
  - `ctype` (character type checking) extension (required);
  - `mbstring` (multibyte string) extension (required);
  - `intl` (internationalization functions) extension (optional, for IDN only).

## Installation

The preferred way to install this extension is through [Composer](https://getcomposer.org).

To install, either run

```sh
php composer.phar require kdn/yii2-domain-validator "*"
```

or add

```text
"kdn/yii2-domain-validator": "*"
```

to the `require` section of your `composer.json` file.

## Usage

Model class example:

```php
<?php

namespace app\models;

use kdn\yii2\validators\DomainValidator;
use Yii;
use yii\base\Model;

class YourCustomModel extends Model
{
    public $domain;

    public function rules()
    {
        return [
            ['domain', DomainValidator::class],
            /*
            or with custom options: enable IDN and forbid URLs
            [
                'domain',
                DomainValidator::class,
                'enableIDN' => true,
                'allowURL' => false,
            ],
            */
        ];
    }

    public function attributeLabels()
    {
        return [
            'domain' => Yii::t('app', 'Domain Name'),
        ];
    }
}
```

Please view public properties in class
[DomainValidator](https://github.com/dmitry-kulikov/yii2-domain-validator/blob/master/src/DomainValidator.php)
to get info about all available options, they documented comprehensively. Here I will highlight only non-evident things.

1. By default, validator allows URL, it will try to parse URL and then validate domain name.
   Note that model attribute value itself will not be modified.
   If URL parsing fails then validator considers value as domain.
   Validator may work not perfect for invalid URLs. For example user input is `http//example.com`,
   the error message will be `Each label of the input value can consist of only letters, numbers and hyphens`,
   although it would be better to show something like `Invalid URL`.
   The problem is that if field allows both URL and bare domain name and the input value is invalid,
   then it is impossible to reliably determine what did user want `http://example.com` or `http.example.com`.
   If you don't need URLs at all, only stand-alone domain name, you can disable this behavior
   by setting `allowURL` to `false`.
   If you always need to validate domain name in URL, no stand-alone domain name,
   then you should add URL validator before domain name validator:

   ```php
   public function rules()
   {
       return [
           ['domain', 'url'],
           ['domain', DomainValidator::class],
       ];
   }
   ```

1. By default, minimum number of domain name labels is 2. So `example` - invalid, `example.com` - valid.
   It is not standard requirement for domain name, standard states that domain name `example` is valid.
   I added this restriction for practical reasons, you can disable it or require even more domain name labels
   using option `labelNumberMin`.
1. Client side validation not implemented, and I have not such plans.
   Please consider [AJAX validation](https://www.yiiframework.com/doc/guide/2.0/en/input-validation#ajax-validation)
   if you want to bring domain validation on client side.

## Testing

Make sure you installed all Composer dependencies (run `composer update` in the base directory of repository).
Run PHPUnit in the base directory of repository:

```sh
./vendor/bin/phpunit
```

### Testing using Docker

#### Requirements

- Docker >= 19.03.0 ([install](https://docs.docker.com/get-docker));
- Docker Compose >= 1.25.5 ([install](https://docs.docker.com/compose/install));
- Docker plugins:
  - buildx ([install](https://github.com/docker/buildx#installing)).

#### Up and running

1. Provide credentials for Composer:

   ```sh
   cp auth.json.example \
       auth.json
   ```

   I suggest to set GitHub OAuth token (also known as personal access token) in `auth.json`,
   however if you have doubts about security, or you are lazy to generate token then you can replace content of
   `auth.json` on `{}`, in most cases this will work.

1. Build images for services:

   ```sh
   docker buildx bake --load --pull
   ```

   or

   ```sh
   docker buildx bake --load --pull --no-cache --progress plain
   ```

   see `docker buildx bake --help` for details.

1. Start service in background mode:

   ```sh
   docker-compose up --detach 8.1
   ```

   This command will start the service with PHP 8.1. Also allowed `7.4`, `5.6`, `8.1-alpine`, `7.4-alpine`
   and `5.6-alpine`, see services defined in `docker-compose.yml`.

1. Execute tests in the running container:

   ```sh
   docker-compose exec 8.1 ./vendor/bin/phpunit
   ```

   Alternatively you can start a shell in the running container and execute tests from it:

   ```sh
   docker-compose exec 8.1 sh
   $ ./vendor/bin/phpunit
   ```

1. Stop and remove containers created by `up`:

   ```sh
   docker-compose down
   ```

   You may want to remove volumes along with containers:

   ```sh
   docker-compose down --volumes
   ```

## Backward compatibility promise

yii2-domain-validator is using [Semver](https://semver.org). This means that versions are tagged
with MAJOR.MINOR.PATCH. Only a new major version will be allowed to break backward
compatibility (BC).

PHP 8 introduced [named arguments](https://wiki.php.net/rfc/named_params), which
increased the cost and reduces flexibility for package maintainers. The names of the
arguments for methods in yii2-domain-validator is not included in our BC promise.
