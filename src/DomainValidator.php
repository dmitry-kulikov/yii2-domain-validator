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
     * @var boolean whether to allow underscores in domain name;
     * defaults to false
     */
    public $allowUnderscore = false;

    /**
     * @var boolean whether to allow URL address along with domain name;
     * defaults to true, meaning that validator should try to parse URL address and then validate domain name
     */
    public $allowURL = true;

    /**
     * @var boolean whether to check whether domain name exists;
     * be aware that this check can fail due to temporary DNS problems even if domain name exists;
     * do not use it to check domain name availability;
     * defaults to false
     */
    public $checkDNS = false;

    /**
     * @var boolean whether validation process should take into account IDN (internationalized domain names);
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
     * @var integer minimum number of domain name labels;
     * defaults to 1, meaning that domain name should contain at least 1 label
     * @see messageLabelNumberMin for the customized message for domain name with too small number of labels
     */
    public $labelNumberMin = 1;

    /**
     * @var string user-defined error message used when DNS record corresponding to domain name not found
     */
    public $messageDNS;

    /**
     * @var string user-defined error message used when domain name contains a character which 'intl' extension
     * failed to convert to ASCII
     */
    public $messageIdnToAscii;

    /**
     * @var string user-defined error message used when domain name contains an invalid character
     */
    public $messageInvalidCharacter;

    /**
     * @var string user-defined error message used when number of domain name labels is smaller than [[labelNumberMin]]
     */
    public $messageLabelNumberMin;

    /**
     * @var string user-defined error message used when domain name label starts or ends with an invalid character
     */
    public $messageLabelStartEnd;

    /**
     * @var string user-defined error message used when domain name label is too long
     */
    public $messageLabelTooLong;

    /**
     * @var string user-defined error message used when domain name label is too short
     */
    public $messageLabelTooShort;

    /**
     * @var string user-defined error message used when domain name is not a string
     */
    public $messageNotString;

    /**
     * @var string user-defined error message used when domain name is too long
     */
    public $messageTooLong;

    /**
     * @var string user-defined error message used when domain name is too short
     */
    public $messageTooShort;

    /**
     * @inheritdoc
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
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return $this->getErrorMessage('messageNotString');
        }

        if ($this->allowURL) {
            $host = parse_url($value, PHP_URL_HOST);
            if (isset($host) && $host !== false) {
                $value = $host;
            }
        }

        if ($this->enableIDN) {
            $asciiValue = idn_to_ascii($value, 0, INTL_IDNA_VARIANT_UTS46, $idnaInfo);
            if ($asciiValue !== false) {
                $value = $asciiValue;
            } else {
                $idnaErrors = null;
                if (is_array($idnaInfo) && array_key_exists('errors', $idnaInfo)) {
                    $idnaErrors = $idnaInfo['errors'];
                }
                if ($idnaErrors & IDNA_ERROR_EMPTY_LABEL) {
                    $errorName = 'messageLabelTooShort';
                } elseif ($idnaErrors & IDNA_ERROR_LABEL_TOO_LONG) {
                    $errorName = 'messageLabelTooLong';
                } elseif ($idnaErrors & IDNA_ERROR_DOMAIN_NAME_TOO_LONG) {
                    $errorName = 'messageTooLong';
                } elseif ($idnaErrors & IDNA_ERROR_LEADING_HYPHEN) {
                    $errorName = 'messageLabelStartEnd';
                } elseif ($idnaErrors & IDNA_ERROR_TRAILING_HYPHEN) {
                    $errorName = 'messageLabelStartEnd';
                } elseif ($idnaErrors & IDNA_ERROR_DISALLOWED) {
                    $errorName = 'messageIdnToAscii';
                } else {
                    if (empty($value)) {
                        $errorName = 'messageTooShort';
                    } else {
                        $errorName = 'messageTooLong';
                    }
                }
                return $this->getErrorMessage($errorName);
            }
        }

        if (empty($value)) {
            return $this->getErrorMessage('messageTooShort');
        }

        // ignore trailing dot
        if ($value[mb_strlen($value, $this->encoding) - 1] == '.') {
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
            return $this->getErrorMessage(
                'messageLabelNumberMin',
                ['labelNumberMin' => $this->labelNumberMin, 'dotsNumberMin' => $this->labelNumberMin - 1]
            );
        }

        for ($i = 0; $i < $labelsCount; $i++) {
            $label = $labels[$i];
            $labelLength = mb_strlen($label, $this->encoding);

            if (empty($label)) {
                return $this->getErrorMessage('messageLabelTooShort');
            }

            if ($this->allowUnderscore) {
                $pattern = '/^[a-z\d-_]+$/i';
            } else {
                $pattern = '/^[a-z\d-]+$/i';
            }
            if (!preg_match($pattern, $label)) {
                return $this->getErrorMessage('messageInvalidCharacter');
            }

            if ($i == $labelsCount - 1) {
                // last domain name label
                if (!ctype_alpha($label[0])) {
                    return $this->getErrorMessage('messageLabelStartEnd');
                }
            } else {
                if (!ctype_alnum($label[0])) {
                    return $this->getErrorMessage('messageLabelStartEnd');
                }
            }
            if (!ctype_alnum($label[$labelLength - 1])) {
                return $this->getErrorMessage('messageLabelStartEnd');
            }

            if ($labelLength > 63) {
                return $this->getErrorMessage('messageLabelTooLong');
            }
        }

        if ($this->checkDNS) {
            if (!checkdnsrr($value, 'ANY')) {
                return $this->getErrorMessage('messageDNS');
            }
        }

        return null;
    }

    /**
     * Get error message by name.
     * @param string $name error message name
     * @param array $params parameters to be inserted into the error message
     * @return string error message.
     */
    protected function getErrorMessage($name, $params = [])
    {
        if (isset($this->$name)) {
            return [$this->$name, $params];
        }
        $this->$name = Yii::t('app', $this->getDefaultErrorMessages()[$name]); // todo app -> kdn-yii2
        return [$this->$name, $params];
    }

    /**
     * Get default error messages.
     * @return array default error messages.
     */
    protected function getDefaultErrorMessages()
    {
        $messages = [
            'messageDNS' => 'DNS record corresponding to {attribute} not found.',
            'messageIdnToAscii' => '{attribute} contains invalid characters.',
            'messageLabelNumberMin' =>
                '{attribute} should consist of at least {labelNumberMin} labels separated by ' .
                '{dotsNumberMin, plural, one{dot} other{dots}}.',
            'messageLabelTooShort' => 'Each label of {attribute} should contain at least 1 character.',
            'messageNotString' => '{attribute} must be a string.',
            'messageTooShort' => '{attribute} should contain at least 1 character.',
        ];
        if ($this->allowUnderscore) {
            if ($this->enableIDN) {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only letters, numbers, hyphens and underscores.';
            } else {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only latin letters, numbers, hyphens and underscores.';
            }
        } else {
            if ($this->enableIDN) {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only letters, numbers and hyphens.';
            } else {
                $messages['messageInvalidCharacter'] =
                    'Each label of {attribute} can consist of only latin letters, numbers and hyphens.';
            }
        }
        if ($this->enableIDN) {
            $messages['messageLabelStartEnd'] =
                'Each label of {attribute} should start and end with letter or number.' .
                ' The rightmost label of {attribute} should start with letter.';
            $messages['messageLabelTooLong'] = 'Label of {attribute} is too long.';
            $messages['messageTooLong'] = '{attribute} is too long.';
        } else {
            $messages['messageLabelStartEnd'] =
                'Each label of {attribute} should start and end with latin letter or number.' .
                ' The rightmost label of {attribute} should start with latin letter.';
            $messages['messageLabelTooLong'] = 'Each label of {attribute} should contain at most 63 characters.';
            $messages['messageTooLong'] = '{attribute} should contain at most 253 characters.';
        }
        return $messages;
    }
}
