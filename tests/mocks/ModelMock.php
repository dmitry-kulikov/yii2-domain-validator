<?php

namespace kdn\yii2\validators\mocks;

use Yii;
use yii\base\Model;

/**
 * Class ModelMock.
 * @package kdn\yii2\validators\mocks
 */
class ModelMock extends Model
{
    public $domain;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ['domain' => Yii::t('kdn/yii2/validators/domain', 'Domain Name')];
    }
}
