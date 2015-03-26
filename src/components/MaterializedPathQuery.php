<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 25.03.15
 * Time: 19:27
 */

namespace matperez\mp\components;


use yii\db\ActiveQuery;

class MaterializedPathQuery extends ActiveQuery
{
    use MaterializedPathQueryTrait;
}