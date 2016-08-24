<?php

namespace kdn\yii2\validators;

use PHPUnit_Framework_TestCase;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class TestCase.
 * @package kdn\yii2\validators
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Mock Yii application.
     */
    protected function setUp()
    {
        parent::setUp();
        static::mockApplication();
    }

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        static::destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application.
     * The application will be destroyed on tearDown() automatically.
     * @param array $config the application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected static function mockApplication($config = [], $appClass = 'yii\console\Application')
    {
        new $appClass(
            ArrayHelper::merge(
                [
                    'id' => 'test-app',
                    'basePath' => __DIR__,
                    'vendorPath' => static::getVendorPath(),
                ],
                $config
            )
        );
    }

    /**
     * Get path to "vendor" directory.
     * @return string path to "vendor" directory.
     */
    protected static function getVendorPath()
    {
        return dirname(__DIR__) . '/vendor';
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected static function destroyApplication()
    {
        Yii::$app = null;
    }
}
