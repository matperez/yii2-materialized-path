<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 01.04.15
 * Time: 12:17
 */

namespace tests\unit;


use Codeception\Specify;
use data\Tree;
use tests\DbTestCase;
use tests\fixtures\TreeFixture;
use yii\helpers\ArrayHelper;

/**
 * Class MaterializedPathBehaviorTest
 * @method Tree tree()
 * @package tests\unit
 */
class MaterializedPathBehaviorTest extends DbTestCase
{
    use Specify;

    public function fixtures()
    {
        return [
            'tree' => TreeFixture::className(),
        ];
    }

    public function testLoadTree()
    {
        $root = $this->tree('root_1');
        $this->specify('it should load tree at once', function() use ($root) {
            expect('root has children', $root->hasChildren())->true();
            $children = $root->getChildren();
            expect('root has two direct children', ArrayHelper::toArray($children))->equals([
                2 => [
                    'id' => 2,
                    'label' => 'node 1.1',
                    'path' => '.1.',
                    'level' => 1,
                    'position' => 1,
                ],
                3 => [
                    'id' => 3,
                    'label' => 'node 1.2',
                    'path' => '.1.',
                    'level' => 1,
                    'position' => 2,
                ],
            ]);
            expect('node 1.1 has one direct children', ArrayHelper::toArray($children[2]->getChildren()))->equals([
                4 => [
                    'id' => 4,
                    'label' => 'node 1.1.1',
                    'path' => '.1.2.',
                    'level' => 2,
                    'position' => 1,
                ],
            ]);
        });
    }

    public function testConvertNodeToRoot()
    {
        $node = $this->tree('node_1_1');
        $this->specify('it should be able to convert existing node into root', function() use ($node) {
            expect('node is transformed into root', $node->makeRoot())->true();
            expect('new root created', Tree::find()->roots()->count())->equals(2);
            expect('node child got level up', ArrayHelper::toArray($node->getChildren()))->equals([
                4 => [
                    'id' => 4,
                    'label' => 'node 1.1.1',
                    'path' => '.2.',
                    'level' => 1,
                    'position' => 1,
                ],
            ]);
        });
    }

    public function testCreateNewRoot()
    {
        $root = new Tree(['label' => 'root 2']);

        $this->specify('it should be able to create new root', function() use ($root) {
            $rootCount = Tree::find()->roots()->count();
            expect('new root created', $root->makeRoot())->true();
            expect('new root appeared in the database', Tree::find()->roots()->count())->equals($rootCount+1);
        });
    }

    public function testAppendTo()
    {
        $root = $this->tree('root_1');
        $this->specify('it should be able to append new node to root', function() use ($root) {
            $child = new Tree(['label' => 'child of root']);
            expect('new node appended to root', $child->appendTo($root))->true();
            expect('new node is a root child', $child->isChildOf($root))->true();
            expect('root is a parent of new node', $root->isParentOf($child))->true();
        });

        $node = $this->tree('node_1_2');
        $this->specify('it should be able to append new node to another node', function() use ($root, $node) {
            $child = new Tree(['label' => 'child of node']);
            expect('new node appended to node', $child->appendTo($node))->true();
            expect('new node is a old node child', $child->isChildOf($node))->true();
            expect('root is one of a parents of a new node', $root->isParentOf($child))->true();
            expect('root is not closest parent of a new node', $root->isParentOf($child, true))->false();
            expect('old node is closest parent of a new node', $node->isParentOf($child, true))->true();
        });
    }

    public function testDelete()
    {
        $root = $this->tree('root_1');
        $this->specify('it should be able to delete nodes with their children', function() use ($root) {
            expect('node is deleted', $root->delete())->equals(1);
            expect('no nodes left in the database', Tree::find()->all())->isEmpty();
        });
    }


}