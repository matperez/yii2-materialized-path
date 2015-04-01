<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 01.04.15
 * Time: 11:01
 */

namespace matperez\mp\components;


use yii\base\Behavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class MaterializedPathBehavior
 * @package src\components
 */
class MaterializedPathBehavior extends Behavior
{
    /**
     * @var int
     */
    public $maxLevel = 32;

    /**
     * @var string
     */
    public $pathAttribute = 'path';

    /**
     * @var string
     */
    public $levelAttribute = 'level';

    /**
     * @var string
     */
    public $positionAttribute = 'position';

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
    public function isRoot()
    {
        return !$this->getParentId();
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return !!count($this->getChildren());
    }

    /**
     * Check that node is leaf
     * @return bool
     */
    public function isLeaf()
    {
        return !$this->hasChildren();
    }

    /**
     * Append node as another node child
     * @param ActiveRecord $node
     * @return ActiveRecord
     */
    public function appendTo($node) {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $this->setPosition(null);
        $children = $this->getChildren();
        if ($node && $node->primaryKey) {
            if ($node->{$this->levelAttribute} == $this->maxLevel) {
                $node = $node->getParent();
            }
            $owner->{$this->levelAttribute} = $node->{$this->levelAttribute} + 1;
            $owner->{$this->pathAttribute}  = $node->{$this->pathAttribute} . $node->primaryKey . '.';
            $owner->{$this->positionAttribute} = count($node->getChildren()) + 1;
            $node->addChild($owner);
        }
        $owner->save();
        $this->_children = [];
        foreach ($children as $child) {
            $child->appendTo($owner);
        }
        return $owner;
    }

    /**
     * Make new root
     * @param bool $new
     * @return ActiveRecord
     */
    public function makeRoot($new = false)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $this->setPosition(null);
        $children = $this->getChildren();
        $owner->{$this->levelAttribute} = 0;
        $owner->{$this->pathAttribute} = '.';
        $rootsCount = $owner->find()->roots()->count();
        $owner->{$this->positionAttribute} = $rootsCount ? $rootsCount + ($new ? 0 : 1) : 0;
        $owner->save();
        $this->_children = [];
        foreach ($children as $child) {
            $child->appendTo($this);
        }
        return $this;
    }

    /**
     * Set node position among siblings
     * @param int $position
     * @return ActiveRecord
     */
    public function setPosition($position = null) {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $path = $this->getParentId() ? $this->getParent()->{$this->pathAttribute} : '.' ;
        $posFrom = (int) $owner->{$this->positionAttribute};
        if ($position) {
            $posTo = (int) $position;
            $lower = $posTo < $posFrom;
            $owner->find()
                ->andWhere(['like', 'path', $path])
                ->andWhere(['level' => $owner->{$this->levelAttribute}])
                ->andWhere(['between', 'position', min($posFrom, $posTo), max($posFrom, $posTo)])
                ->createCommand()->update($owner->tableName(), [
                    'position' => new Expression('position' . ($lower ? '+' : '-') . 1)
                ]);
            $owner->{$this->positionAttribute} = $position;
            $owner->update(true, ['position']);
        } else {
            $owner->find()
                ->andWhere(['like', 'path', $path])
                ->andWhere(['level' => $owner->{$this->levelAttribute}])
                ->andWhere(['>', 'position', $posFrom])
                ->createCommand()->update($owner->tableName(), [
                    'position' => new Expression('position - 1')
                ]);
        }
        return $this;
    }


    /**
     * Load whole tree at once
     * @param ActiveQuery $query
     * @param bool $forceReload
     * @return ActiveRecord
     */
    public function loadTree(ActiveQuery $query = null, $forceReload = false) {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        if ($this->_treeIsLoaded && !$forceReload)
            return $owner;
        $this->_treeIsLoaded = true;
        $query || $query = $owner->find();
        if ($owner->{$this->pathAttribute} || $owner->primaryKey) {
            $path = $owner->primaryKey ? ".{$owner->primaryKey}." : $owner->{$this->pathAttribute};
            $query->andWhere(['like', 'path', $path]);
        } else {
            return $owner;
        }
        $query->orderBy(['position' => SORT_ASC]);
        $items = $query->all();
        $levels = [];
        foreach($items as $item) {
            $l = $item->{$this->levelAttribute};
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
        return $owner;
    }

    /**
     * Get node children
     * @return ActiveRecord[]
     */
    public function getChildren() {
        if(!$this->_treeIsLoaded)
            return $this->loadTree()->getChildren();
        return $this->_children;
    }

    /**
     * Set node children
     * @param ActiveRecord[] $children
     */
    public function setChildren($children)
    {
        $this->_children = $children;
    }

    /**
     * Add node as a child
     * @param ActiveRecord $node
     * @return ActiveRecord
     */
    public function addChild($node) {
        if ($node->primaryKey) {
            $this->_children[$node->primaryKey] = $node;
        }
        return $this->owner;
    }

    /**
     * Add descendant node
     * @param ActiveRecord $node
     */
    public function addDescendant($node) {
        if ($this->isParentOf($node)) {
            $this->addChild($node);
        } else if ($child = $this->getChildParentOf($node)) {
            $child->addDescendant($node);
        }
    }

    /**
     * Check if node is child of current
     * @param ActiveRecord $node
     * @return bool
     */
    public function isChildOf($node) {
        return $node->isParentOf($this->owner);
    }

    /**
     * @return ActiveRecord
     */
    public function getParent()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        if ($this->getParentId()) {
            $primaryKey = $owner->primaryKey()[0];
            return $owner->find()
                ->andWhere([$primaryKey => $this->getParentId()])
                ->one();
        }
        return null;
    }

    /**
     * Get closest parent id
     * @return mixed
     */
    public function getParentId() {
        $ids = $this->getParentIds();
        return array_pop($ids);
    }

    /**
     * Get parent ids array
     * @return array
     */
    public function getParentIds()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $ids = explode('.', $owner->{$this->pathAttribute});
        array_pop($ids);
        foreach ($ids as &$id) {
            $id = (int) $id;
        }
        return $ids;
    }

    /**
     * Check that node is parent of current
     * @param ActiveRecord $node
     * @param bool $closestOnly
     * @return bool
     */
    public function isParentOf(ActiveRecord $node, $closestOnly = false) {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        return $closestOnly ?
            $owner->primaryKey == $node->getParentId() :
            in_array($owner->primaryKey, $node->getParentIds());
    }

    /**
     * @param ActiveRecord $node
     * @return ActiveRecord
     */
    public function getChildParentOf($node) {
        foreach ($this->_children as $child) {
            if (in_array($child->primaryKey, $node->getParentIds())) {
                return $child;
            }
        }
        return null;
    }

}