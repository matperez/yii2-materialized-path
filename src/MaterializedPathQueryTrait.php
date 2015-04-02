<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 25.03.15
 * Time: 19:20
 */

namespace matperez\mp;

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