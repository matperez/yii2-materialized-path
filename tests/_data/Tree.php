<?php

namespace data;

use matperez\mp\MaterializedPathBehavior;
use matperez\mp\MaterializedPathQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tree".
 *
 * @property integer $id
 * @property string $label
 * @property string $path
 * @property integer $position
 * @property integer $level
 */
class Tree extends ActiveRecord
{

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
    public function behaviors()
    {
        return [
            [
                'class' => MaterializedPathBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label'], 'required'],
            [['position', 'level'], 'integer'],
            [['label', 'path'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Name',
            'path' => 'Path',
            'position' => 'Position',
            'level' => 'Level',
        ];
    }


    /**
     * Query factory
     * @return MaterializedPathQuery
     */
    public static function find()
    {
        return new MaterializedPathQuery(get_called_class());
    }

}
