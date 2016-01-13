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
 * @method Tree tree($name)
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

    /**
     * @test
     */
    public function it_should_load_tree_at_once()
    {
        /** @var Tree $root */
        $root = $this->tree('root_1');
        self::assertTrue($root->hasChildren(), 'root has children');

        $children = $root->getChildren();

        $firstLevelChildren = [
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
        ];
        self::assertEquals($firstLevelChildren, ArrayHelper::toArray($children), 'root has two direct children');

        $secondLevelChildren = [
            4 => [
                'id' => 4,
                'label' => 'node 1.1.1',
                'path' => '.1.2.',
                'level' => 2,
                'position' => 1,
            ],
        ];
        self::assertEquals($secondLevelChildren, ArrayHelper::toArray($children[2]->getChildren()), 'node 1.1 has one direct children');
    }

    /**
     * @test
     */
    public function it_should_be_able_to_convert_existing_node_to_root()
    {
        $node = $this->tree('node_1_1');
        self::assertEquals(1, Tree::find()->roots()->count(), 'there is only one root for now');
        self::assertTrue($node->makeRoot(), 'node is transformed to root');
        self::assertEquals(2, Tree::find()->roots()->count(), 'new root has been created');

        $childData = [
            4 => [
                'id' => 4,
                'label' => 'node 1.1.1',
                'path' => '.2.',
                'level' => 1,
                'position' => 1,
            ],
        ];
        self::assertEquals($childData, ArrayHelper::toArray($node->getChildren()), 'node child got level up');
    }

    /**
     * @test
     */
    public function it_should_create_new_root()
    {
        $root = new Tree(['label' => 'root 2']);
        $rootCount = Tree::find()->roots()->count();
        expect('new root created', $root->makeRoot())->true();
        expect('new root appeared in the database', Tree::find()->roots()->count())->equals($rootCount+1);
    }

    /**
     * @test
     */
    public function it_should_append_new_node_to_root()
    {
        $root = $this->tree('root_1');
        $child = new Tree(['label' => 'child of root']);
        expect('new node appended to root', $child->appendTo($root))->true();
        expect('new node is a root child', $child->isChildOf($root))->true();
        expect('root is a parent of new node', $root->isParentOf($child))->true();
    }

    /**
     * @test
     */
    public function it_should_append_new_node_to_another_node()
    {
        $root = $this->tree('root_1');
        $node = $this->tree('node_1_2');
        $child = new Tree(['label' => 'child of node']);
        expect('new node appended to node', $child->appendTo($node))->true();
        expect('new node is a old node child', $child->isChildOf($node))->true();
        expect('root is one of a parents of a new node', $root->isParentOf($child))->true();
        expect('root is not closest parent of a new node', $root->isParentOf($child, true))->false();
        expect('old node is closest parent of a new node', $node->isParentOf($child, true))->true();
    }

    /**
     * @test
     */
    public function it_should_delete_nodes_with_their_children()
    {
        $root = $this->tree('root_1');
        expect('node is deleted', $root->delete())->equals(1);
        expect('no nodes left in the database', Tree::find()->all())->isEmpty();
    }

    /**
     * @test
     */
    public function i_can_create_adjusted_items_list()
    {
        $root = $this->tree('root_1');
        $expected = [
            1 => 'root 1',
            2 => ' node 1.1',
            3 => ' node 1.2',
            4 => '  node 1.1.1',
        ];
        $items = [
            $root->id => $root->label,
        ];
        foreach ($root->children as $child) {
            $label = str_repeat(' ', $child->level).$child->label;
            $items[$child->id] = $label;
            if ($child->hasChildren()) {
                foreach ($child->children as $subChild) {
                    $label = str_repeat(' ', $subChild->level).$subChild->label;
                    $items[$subChild->id] = $label;
                }
            }
        }
        self::assertEquals($expected, $items);
    }
}