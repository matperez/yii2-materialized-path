<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 25.03.15
 * Time: 19:20
 */

namespace matperez\mp\components;

use yii\db\ActiveQuery;

trait MaterializedPathQueryTrait
{
    /**
     * Get root nodes
     * @return ActiveQuery|MaterializedPathQueryTrait
     */
    public function roots()
    {
        /** @var ActiveQuery $this */
        $this->andWhere(['path' => '.']);
        return $this;
    }
}