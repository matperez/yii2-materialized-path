<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 02.04.15
 * Time: 22:50
 */

namespace tests;


class DbTestCase extends \yii\codeception\DbTestCase
{
    /**
     * @var string
     */
    public $appConfig = '@tests/config/unit.php';
}