<?php

namespace matperez\mp\models;

use Yii;

/**
 * This is the model class for table "tree".
 *
 * @property integer $id
 * @property string $name
 * @property string $path
 * @property integer $position
 * @property integer $level
 */
class Tree extends \yii\db\ActiveRecord
{
    use \matperez\mp\components\MaterializedPathTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tree';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['position', 'level'], 'integer'],
            [['name', 'path'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'path' => 'Path',
            'position' => 'Position',
            'level' => 'Level',
        ];
    }

}
