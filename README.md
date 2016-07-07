# yii2-domain-validator

Domain validator for Yii 2.

# Requirements

- PHP 5.4 or later;
- Yii framework 2;
- PHP extensions:
  - `ctype` (character type checking) extension (required);
  - `mbstring` (multibyte string) extension (required);
  - `intl` (internationalization functions) extension (optional, for IDN only).

# Installation

The preferred way to install this extension is through [composer](https://getcomposer.org).

To install, either run

```
$ php composer.phar require kdn/yii2-domain-validator "*"
```

or add

```
"kdn/yii2-domain-validator": "*"
```

to the `require` section of your `composer.json` file.

# Usage

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
            ['domain', DomainValidator::className()],
            /*
            or with custom options: enable IDN and forbid URLs
            [
                'domain',
                DomainValidator::className(),
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

1) By default validator allows URL, it will try to parse URL and then validate domain name.
If URL parsing fails then validator considers value as domain.
If you don't need URLs at all, only stand-alone domain name, you can disable this behavior
by setting `allowURL` to `false`.
If you always need to validate domain name in URL, no stand-alone domain name,
then you should add URL validator before domain name validator:
```php
public function rules()
{
    return [
        ['domain', 'url'],
        ['domain', DomainValidator::className()],
    ];
}

```

2) By default minimum number of domain name labels is 2. So `example` - invalid, `example.com` - valid.
It is not standard requirement for domain name, standard states that domain name `example` is valid.
I added this restriction for practical reasons, you can disable it or require even more domain name labels
using option `labelNumberMin`.

3) Client side validation not implemented and I have not such plans.
Please consider [AJAX validation](http://www.yiiframework.com/doc-2.0/guide-input-validation.html#ajax-validation)
if you want to bring domain validation on client side.
