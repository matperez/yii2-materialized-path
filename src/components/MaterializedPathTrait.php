<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 25.03.15
 * Time: 19:44
 */

namespace matperez\mp\components;


use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class MaterializedPathTrait
 * @property string $path
 * @property integer $position
 * @property integer $level
 *
 * @property integer $maxLevel
 * @property mixed $parentId
 * @property ActiveRecord|MaterializedPathTrait $parent
 * @property array $parentIds
 * @property bool $hasChildren
 * @property bool $isRoot
 * @property bool $isLeaf
 * @property ActiveRecord|MaterializedPathTrait[] $children
 *
 * @package app\components
 */
trait MaterializedPathTrait
{
    /**
     * @var int
     */
    private $_maxLevel = 32;

    /**
     * @var array
     */
    private $_children = [];

    /**
     * @var bool
     */
    private $_treeIsLoaded = false;

    /**
     * Check that node is a root
     * @return bool
     */
    public function getIsRoot()
    {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        return !$this->parentId;
    }

    /**
     * Check that node is leaf
     * @return bool
     */
    public function getIsLeaf()
    {
        return !$this->hasChildren;
    }

    /**
     * @return bool
     */
    public function getHasChildren() {
        return !!count($this->children);
    }

    /**
     * Move to parent node
     * @param ActiveRecord|MaterializedPathTrait $node
     * @return ActiveRecord|MaterializedPathTrait
     */
    public function appendTo($node) {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        $this->setPosition(null);
        $children = $this->children;
        if ($node && $node->primaryKey) {
            if ($node->level == $this->maxLevel) {
                $node = $node->parent;
            }
            $this->level = $node->level + 1;
            $this->path  = $node->path . $node->primaryKey . '.';
            $this->position = count($node->children) + 1;
            $node->addChild($this);
        }
        $this->save();
        $this->_children = array();
        foreach ($children as $child) {
            $child->appendTo($this);
        }
        return $this;
    }

    /**
     * Make new root
     * @param bool $new
     * @return $this
     */
    public function makeRoot($new = false)
    {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        $this->setPosition(null);
        $children = $this->children;
        $this->level = 0;
        $this->path = '.';
        $rootsCount = self::find()->roots()->count();
        $this->position = $rootsCount ? $rootsCount + ($new ? 0 : 1) : 0;
        $this->save();
        $this->_children = array();
        foreach ($children as $child) {
            $child->appendTo($this);
        }
        return $this;
    }

    /**
     * Set node position among siblings
     * @param int|null $position
     * @return ActiveRecord|MaterializedPathTrait
     */
    public function setPosition($position = null) {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        $path = $this->parentId ? $this->parent->path : '.' ;
        $posFrom = (int) $this->position;
        if ($position) {
            $posTo = (int) $position;
            $lower = $posTo < $posFrom;
            self::find()
                ->andWhere(['like', 'path', $path])
                ->andWhere(['level' => $this->level])
                ->andWhere(['between', 'position', min($posFrom, $posTo), max($posFrom, $posTo)])
                ->createCommand()->update($this->tableName(), [
                    'position' => new Expression('position' . ($lower ? '+' : '-') . 1)
                ]);
            $this->position = $position;
            $this->update(true, ['position']);
        } else {
            self::find()
                ->andWhere(['like', 'path', $path])
                ->andWhere(['level' => $this->level])
                ->andWhere(['>', 'position', $posFrom])
                ->createCommand()->update($this->tableName(), [
                    'position' => new Expression('position - 1')
                ]);
        }
        return $this;
    }


    /**
     * Load whole tree at once
     * @param ActiveQuery $query additional search criteria
     * @param bool $forceReload
     * @return $this
     */
    public function loadTree($query = null, $forceReload = false) {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        if ($this->_treeIsLoaded && !$forceReload)
            return $this;
        $this->_treeIsLoaded = true;
        $query || $query = self::find();
        if ($this->path || $this->primaryKey) {
            $path = $this->primaryKey ? ".{$this->primaryKey}." : $this->path;
            $query->andWhere(['like', 'path', $path]);
        } else {
            return $this;
        }
        $query->orderBy(['position' => SORT_ASC]);
        $items = $query->all();
        $levels = [];
        foreach($items as $item) {
            /** @var ActiveRecord|MaterializedPathTrait $item */
            $l = $item->level;
            if (empty($levels[$l]))
                $levels[$l] = [];
            $levels[$l][] = $item;
        }
        ksort($levels);
        foreach($levels as $level) {
            foreach($level as $element) {
                $this->addDescendant($element);
            }
        }
        return $this;
    }

    /**
     * Get node children
     * @return ActiveRecord|MaterializedPathTrait[]
     */
    public function getChildren() {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        if(!$this->_treeIsLoaded)
            return $this->loadTree()->getChildren();
        return $this->_children;
    }

    /**
     * Set node children
     * @param ActiveRecord|MaterializedPathTrait[] $children array of nodes
     */
    public function setChildren($children)
    {
        $this->_children = $children;
    }

    /**
     * Add node as a child
     * @param ActiveRecord|MaterializedPathTrait $node
     * @return $this
     */
    public function addChild($node) {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        $this->_children[$node->primaryKey] = $node;
        return $this;
    }

    /**
     * Add descendant node
     * @param ActiveRecord|MaterializedPathTrait $node
     * @return $this
     */
    public function addDescendant($node) {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        if ($this->isParentOf($node)) {
            $this->addChild($node);
        } else if ($child = $this->getChildParentOf($node)) {
            $child->addDescendant($node);
        }
    }

    /**
     * Check if node is child of current
     * @param ActiveRecord|MaterializedPathTrait $node
     * @return bool
     */
    public function isChildOf($node) {
        return $node->isParentOf($this);
    }

    /**
     * Get closest parent of the node
     * @return ActiveRecord|MaterializedPathTrait
     */
    public function getParent()
    {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        if ($this->parentId) {
            return self::find()
                ->andWhere([$this->primaryKey()[0] => $this->parentId])
                ->one();
        }
        return null;
    }

    /**
     * Get closest parent id
     * @return mixed
     */
    public function getParentId() {
        $ids = $this->parentIds;
        return array_pop($ids);
    }

    /**
     * Get parent ids array
     * @return array
     */
    public function getParentIds()
    {
        $ids = explode('.', $this->path);
        array_pop($ids);
        foreach ($ids as &$id) {
            $id = (int) $id;
        }
        return $ids;
    }

    /**
     * Check that node is parent of current
     * @param ActiveRecord|MaterializedPathTrait $node
     * @param bool $closestOnly - check all of parents, not the closest one
     * @return bool
     */
    public function isParentOf($node, $closestOnly = false) {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        return $closestOnly ?
            $this->primaryKey == $node->getParentId() :
            in_array($this->primaryKey, $node->parentIds);
    }

    /**
     * @param ActiveRecord|MaterializedPathTrait $node
     * @return ActiveRecord|MaterializedPathTrait|null
     */
    public function getChildParentOf($node) {
        /** @var ActiveRecord|MaterializedPathTrait $this */
        foreach ($this->_children as $child) {
            if (in_array($child->primaryKey, $node->parentIds)) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Query factory
     * @return MaterializedPathQuery
     */
    public static function find()
    {
        return new MaterializedPathQuery(get_called_class());
    }

    /**
     * Maximum depth of tree
     * @return int
     */
    public function getMaxLevel()
    {
        return $this->_maxLevel;
    }

}