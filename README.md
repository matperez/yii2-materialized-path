# Materialized Path for Yii 2

Materialized Path Tree trait for Yii2 ActiveRecord.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require matperez/yii2-materialized-path
```

or add

```
"matperez/yii2-materialized-path": "*"
```

to the `require` section of your `composer.json` file.


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

Configure model as follow:

```php

use matperez\mp\MaterializedPathBehavior;
use matperez\mp\MaterializedPathQuery;

class Tree extends \yii\db\ActiveRecord
{
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
            [['position', 'level'], 'integer'],
            [['path'], 'string', 'max' => 255]
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
$root->appendTo($root);
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

### Delete node with children

To delete node with children
```php
$node->delete();
```

### Todo

more tests, mode examples