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
use tests\TestCase;

class BehaviorTest extends TestCase
{
    use Specify;

    public function testCreateRoot()
    {
        $root = new Tree(['label' => 'new root']);

        $this->specify('it should be able to create new root', function() use ($root) {
            $root->makeRoot();
            expect('path is dot', $root->path)->equals('.');
            expect('position is one', $root->position)->equals(0);
            expect('level is 0', $root->level)->equals(0);
            expect('it is root', $root->isRoot())->true();
        });
    }

    public function testCreateChild()
    {
        /** @var Tree $root */
        $root = new Tree(['label' => 'new root']);
        $root->makeRoot();

        $child = new Tree(['label' => 'new child']);

        $this->specify('it should be able to create new child', function() use ($root, $child) {
            expect('it is not child of root', $child->isChildOf($root))->false();
            $child->appendTo($root);
            expect('path contain root id', $child->path)->equals(".{$root->id}.");
            expect('position is zero', $child->position)->equals(1);
            expect('level is 1', $child->level)->equals(1);

            expect('it is child of root', $child->isChildOf($root))->true();
            expect('parent id is root id', $child->getParentId())->equals($root->id);
            expect('parent is root', $child->getParent()->id)->equals($root->id);
        });
    }

    public function testLoadTree()
    {
        $root = new Tree(['label' => 'root']);
        $child = new Tree(['label' => 'child']);
        $root->makeRoot();
        $child->appendTo($root);
        $this->specify('it should load tree at once', function() use ($root, $child) {
            $root->loadTree(null, true);
            $children = $root->getChildren();
            expect($children)->notEmpty();
        });
    }
}