# Materialized Path for Yii 2

Materialized Path Tree trait for Yii2 ActiveRecord.

## Installation

git clone && use. composer is not available. 

## Migrations

Run the following command

```bash
$ yii migrate/create create_tree_table
```

Open the `/path/to/migrations/m_xxxxxx_xxxxxx_create_tree_table.php` file,
inside the `up()` method add the following

```php
$this->createTable('tree', [
    'id' => Schema::TYPE_PK,
    'name' => Schema::TYPE_STRING.' NOT NULL',
    'path' => Schema::TYPE_STRING.' NOT NULL DEFAULT \'.\'',
    'position' => Schema::TYPE_INTEGER.' NOT NULL DEFAULT 0',
    'level' => Schema::TYPE_INTEGER.' NOT NULL DEFAULT 0',
]);
```

## Configuring

There are no configurable parameters. Attach trait to your model and use it. 

```php
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
```

## Usage

### Making a root node

To make a root node

```php
$root = new Tree(['name' => 'root']);
$root->makeRoot();
```

The tree will look like this

```
- root
```

### Appending a node as the child of another node

To prepend a node as the first child of another node

```php
$child = new Tree(['name' => 'child']);
$russia->appendTo($root);
```

The tree will look like this

```
- root
    - child
```

### Move node up and down among siblings

```php
$node = Tree::findOne(['name' => 'child']);
// move node up
$node->setPosition($node->position - 1);
// move node down
$node->setPosition($node->position + 1);
```

### Getting the root nodes

To get all the root nodes

```php
$roots = Tree::find()->roots()->all();
```

### Getting children of a node

To get all the children of a node

```php
$root = Tree::find()->roots()->one();
foreach($root->children as $child) {
    foreach($child->children as $subchild) {
        // do the things with a subchild    
    }
}
```

### Getting parents of a node

To get all the parents of a node

```php
$node = Tree::findOne(['name' => 'child']);
$parents = Tree::find()->andWhere(['id' => $node->parentIds])->all();
```

To get the first parent of a node

```php
$node = Tree::findOne(['name' => 'child']);
$parent = $node->parent();
```

### Todo

composer, tests