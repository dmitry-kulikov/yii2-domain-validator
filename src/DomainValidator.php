<?php

namespace kdn\yii2\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\validators\Validator;

/**
 * Class DomainValidator.
 * @package kdn\yii2\validators
 */
class DomainValidator extends Validator
{
    /**
     * @var bool whether to allow underscores in domain name;
     * defaults to false
     */
    public $allowUnderscore = false;

    /**
     * @var bool whether to allow URL address along with domain name;
     * defaults to true, meaning that validator should try to parse URL address and then validate domain name
     */
    public $allowURL = true;

    /**
     * @var bool|callable whether to check whether domain name exists;
     * be aware that this check can fail due to temporary DNS problems even if domain name exists;
     * do not use it to check domain name availability;
     * defaults to false;
     * this field can be specified as a PHP callback, for example:
     * ```php
     * function (string $domain) {
     *     $records = @dns_get_record("$domain.", DNS_MX); // @ is just for simplicity of example, avoid to use it
     *     if (empty($records)) {
     *         return ['Cannot find Mail Exchanger record for "{value}".', ['value' => $domain]];
     *     }
     *
     *     return null; // the data is valid
     * }
     * ```
     * note that alternatively you can override method `checkDNS`
     * @see checkDNS
     */
    public $checkDNS = false;

    /**
     * @var bool whether validation process should take into account IDN (internationalized domain names);
     * defaults to false, meaning that validation of domain names containing IDN will always fail;
     * note that in order to use IDN validation you have to install and enable `intl` PHP extension,
     * otherwise an exception would be thrown
     */
    public $enableIDN = false;

    /**
     * @var string the encoding of the string value to be validated (e.g. 'UTF-8');
     * if this property is not set, [[\yii\base\Application::charset]] will be used
     */
    public $encoding;

    /**
     * @var string the base path for all translated messages; specify it if you want to use custom translated messages
     */
    public $i18nBasePath;

    /**
     * @var int minimum number of domain name labels;
     * defaults to 2, meaning that domain name should contain at least 2 labels
     * @see messageLabelNumberMin for the customized message for domain name with too small number of labels
     */
    public $labelNumberMin = 2;

    /**
     * @var string user-defined error message used when domain name is invalid but
     * reason is too complicated for explanation to end-user or details are not needed at all;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     * @see simpleErrorMessage to use this message for all possible errors
     */
    public $message;

    /**
     * @var string user-defined error message used when DNS record corresponding to domain name not found;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageDNS;

    /**
     * @var string user-defined error message used when domain name contains an invalid character;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageInvalidCharacter;

    /**
     * @var string user-defined error message used when number of domain name labels is smaller than [[labelNumberMin]];
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{labelNumberMin}`: the value of [[labelNumberMin]]
     * - `{value}`: the value of the attribute being validated
     */
    public $messageLabelNumberMin;

    /**
     * @var string user-defined error message used when domain name label starts or ends with an invalid character;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageLabelStartEnd;

    /**
     * @var string user-defined error message used when domain name label is too long;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageLabelTooLong;

    /**
     * @var string user-defined error message used when domain name label is too short;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageLabelTooShort;

    /**
     * @var string user-defined error message used when domain name is not a string;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageNotString;

    /**
     * @var string user-defined error message used when domain name is too long;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageTooLong;

    /**
     * @var string user-defined error message used when domain name is too short;
     * you may use the following placeholders in the message:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $messageTooShort;

    /**
     * @var bool whether to always use simple error message;
     * defaults to false, meaning that validator should use specialized error messages for different errors,
     * it should help end-user to understand reason of error; set it to true if detailed error messages don't fit
     * for your application then [[message]] will be used in all cases
     */
    public $simpleErrorMessage = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->enableIDN && !function_exists('idn_to_ascii')) {
            throw new InvalidConfigException(
                'In order to use IDN validation intl extension must be installed and enabled.'
            );
        }
        if (!isset($this->encoding)) {
            $this->encoding = Yii::$app->charset;
        }
        if (!isset($this->i18nBasePath)) {
            $this->i18nBasePath = dirname(__DIR__) . '/messages';
        }
        Yii::$app->i18n->translations['kdn/yii2/validators/domain'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => $this->i18nBasePath,
            'fileMap' => ['kdn/yii2/validators/domain' => 'domain.php'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return $this->getErrorMessage('messageNotString');
        }

        if (empty($value)) {
            return $this->getErrorMessage('messageTooShort');
        }

        if ($this->allowURL) {
            $host = parse_url($value, PHP_URL_HOST);
            if (isset($host) && $host !== false) {
                $value = $host;
            }
        }

        if ($this->enableIDN) {
            $idnaInfo = null;
            $options = IDNA_CHECK_BIDI | IDNA_CHECK_CONTEXTJ;
            $asciiValue = idn_to_ascii($value, $options, INTL_IDNA_VARIANT_UTS46, $idnaInfo);
            if ($asciiValue !== false) {
                $value = $asciiValue;
            } else {
                $idnaErrors = null;
                if (is_array($idnaInfo) && array_key_exists('errors', $idnaInfo)) {
                    $idnaErrors = $idnaInfo['errors'];
                }
                if ($idnaErrors & IDNA_ERROR_DOMAIN_NAME_TOO_LONG) {
                    $errorMessageName = 'messageTooLong';
                } elseif ($idnaErrors & IDNA_ERROR_EMPTY_LABEL) {
                    $errorMessageName = 'messageLabelTooShort';
                } elseif ($idnaErrors & IDNA_ERROR_LABEL_TOO_LONG) {
                    $errorMessageName = 'messageLabelTooLong';
                } elseif ($idnaErrors & IDNA_ERROR_DISALLOWED) {
                    $errorMessageName = 'messageInvalidCharacter';
                } elseif ($idnaErrors & IDNA_ERROR_LEADING_HYPHEN || $idnaErrors & IDNA_ERROR_TRAILING_HYPHEN) {
                    $errorMessageName = 'messageLabelStartEnd';
                } elseif (empty($idnaInfo)) {
                    // too long domain name caused buffer overflow
                    $errorMessageName = 'messageTooLong';
                } else {
                    $errorMessageName = 'message';
                }
                return $this->getErrorMessage($errorMessageName);
            }
        }

        // ignore trailing dot
        if (mb_substr($value, -1, 1, $this->encoding) == '.') {
            $value = substr_replace($value, '', -1);
        }

        // 253 characters limit is same as 127 levels,
        // domain name with 127 levels with 1 character per label will be 253 characters long
        if (mb_strlen($value, $this->encoding) > 253) {
            return $this->getErrorMessage('messageTooLong');
        }

        $labels = explode('.', $value);
        $labelsCount = count($labels);

        if ($labelsCount < $this->labelNumberMin) {
            return $this->getErrorMessage('messageLabelNumberMin', ['labelNumberMin' => $this->labelNumberMin]);
        }

        for ($i = 0; $i < $labelsCount; $i++) {
            $label = $labels[$i];
            $labelLength = mb_strlen($label, $this->encoding);

            if (empty($label)) {
                return $this->getErrorMessage('messageLabelTooShort');
            }

            if ($labelLength > 63) {
                return $this->getErrorMessage('messageLabelTooLong');
            }

            if ($this->allowUnderscore) {
                $pattern = '/^[a-z\d\-_]+$/i';
            } else {
                $pattern = '/^[a-z\d\-]+$/i';
            }
            if (!preg_match($pattern, $label)) {
                return $this->getErrorMessage('messageInvalidCharacter');
            }

            if ($i == $labelsCount - 1 && !ctype_alpha($label[0])
                || !ctype_alnum($label[0])
                || !ctype_alnum($label[$labelLength - 1])
            ) {
                return $this->getErrorMessage('messageLabelStartEnd');
            }
        }

        if ($this->checkDNS) {
            if (is_callable($this->checkDNS)) {
                return call_user_func($this->checkDNS, $value);
            }

            if (!$this->checkDNS($value)) {
                return $this->getErrorMessage('messageDNS');
            }
        }

        return null;
    }

    /**
     * Check whether domain name exists.
     * @param string $value domain name
     * @return bool whether domain name exists.
     * @see https://github.com/yiisoft/yii2/issues/17083
     */
    protected function checkDNS($value)
    {
        $value = "$value.";
        if (!checkdnsrr($value, 'ANY')) {
            return false;
        }

        $records = dns_get_record($value, DNS_ANY);
        return !empty($records);
    }

    /**
     * Get error message by name.
     * @param string $name error message name
     * @param array $params parameters to be inserted into the error message
     * @return array error message.
     */
    protected function getErrorMessage($name, $params = [])
    {
        if ($this->simpleErrorMessage) {
            $name = 'message';
        }
        if (isset($this->$name)) {
            return [$this->$name, $params];
        }
        $this->$name = Yii::t('kdn/yii2/validators/domain', $this->getDefaultErrorMessages()[$name]);
        return [$this->$name, $params];
    }

    /**
     * Get default error messages.
     * @return array default error messages.
     */
    protected function getDefaultErrorMessages()
    {
        $messages = [
            'message' => '{attribute} is invalid.',
            'messageDNS' => 'DNS record corresponding to {attribute} not found.',
            'messageLabelNumberMin' =>
                '{attribute} should consist of at least {labelNumberMin, number} labels separated by ' .
                '{labelNumberMin, plural, =2{dot} other{dots}}.',
            'messageLabelTooShort' => 'Each label of {attribute} should contain at least 1 character.',
            'messageNotString' => '{attribute} must be a string.',
            'messageTooShort' => '{attribute} should contain at least 1 character.',
        ];
        if ($this->enableIDN) {
            $messages['messageLabelStartEnd'] =
                'Each label of {attribute} should start and end with letter or number.' .
                ' The rightmost label of {attribute} should start with letter.';
            $messages['messageLabelTooLong'] = 'Label of {attribute} is too long.';
            $messages['messageTooLong'] = '{attribute} is too long.';
            if ($this->allowUnderscore) {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only letters, numbers, hyphens and underscores.';
            } else {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only letters, numbers and hyphens.';
            }
        } else {
            $messages['messageLabelStartEnd'] =
                'Each label of {attribute} should start and end with latin letter or number.' .
                ' The rightmost label of {attribute} should start with latin letter.';
            $messages['messageLabelTooLong'] = 'Each label of {attribute} should contain at most 63 characters.';
            $messages['messageTooLong'] = '{attribute} should contain at most 253 characters.';
            if ($this->allowUnderscore) {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only latin letters, numbers, hyphens and underscores.';
            } else {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only latin letters, numbers and hyphens.';
            }
        }
        return $messages;
    }
}
