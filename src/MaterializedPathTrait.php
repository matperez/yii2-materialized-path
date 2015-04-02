<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 25.03.15
 * Time: 19:44
 */

namespace matperez\mp;


use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class MaterializedPathTrait
 * @property ActiveRecord|MaterializedPathTrait[] $children
 * @package matperez\mp
 */
trait MaterializedPathTrait
{
    /**
     * Check that node is a root
     * @return bool
     */
    public function isRoot()
    {
        return parent::isRoot();
    }

    /**
     * Check that node is leaf
     * @return bool
     */
    public function isLeaf()
    {
        return parent::isLeaf();
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return parent::hasChildren();
    }

    /**
     * Append node as another node child
     * @see MaterializedPathBehavior::appendTo()
     * @param ActiveRecord $node
     * @param bool $runValidation
     * @param array $attributes
     * @return bool
     */
    public function appendTo($node, $runValidation = true, $attributes = null)
    {
        return parent::appendTo($node, $runValidation, $attributes);
    }

    /**
     * Make new root
     * @see MaterializedPathBehavior::makeRoot()
     * @param bool $runValidation
     * @param array $attributes
     * @return bool
     */
    public function makeRoot($runValidation = true, $attributes = null)
    {
        return parent::makeRoot($runValidation, $attributes);
    }

    /**
     * Set node position among siblings
     * @see MaterializedPathBehavior::setPosition()
     * @param int|null $position
     * @return ActiveRecord|MaterializedPathTrait
     */
    public function setPosition($position = null)
    {
        return parent::setPosition($position);
    }


    /**
     * Load whole tree at once
     * @see MaterializedPathBehavior::loadTree()
     * @param ActiveQuery $query additional search criteria
     * @param bool $forceReload
     * @return $this
     */
    public function loadTree($query = null, $forceReload = false)
    {
        return parent::loadTree($query, $forceReload);
    }

    /**
     * Get node children
     * @see MaterializedPathBehavior::getChildren()
     * @return ActiveRecord[]
     */
    public function getChildren()
    {
        return parent::getChildren();
    }

    /**
     * Set node children
     * @param ActiveRecord[] $children array of nodes
     */
    public function setChildren($children)
    {
        return parent::setChildren($children);
    }

    public function addChild($node)
    {
        return parent::addChild($node);
    }

    public function addDescendant($node)
    {
        return parent::addDescendant($node);
    }

    public function isChildOf($node)
    {
        return parent::isChildOf($node);
    }

    public function getParent()
    {
        return parent::getParent();
    }

    public function getParentId()
    {
        return parent::getParentId();
    }

    public function getParentIds()
    {
        return parent::getParentIds();
    }

    public function isParentOf($node, $closestOnly = false)
    {
        return parent::isParentOf($node, $closestOnly);
    }

    public function getChildParentOf($node)
    {
        return parent::getChildParentOf($node);
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