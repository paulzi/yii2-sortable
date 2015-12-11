<?php
/**
 * @link https://github.com/paulzi/yii2-sortable
 * @copyright Copyright (c) 2015 PaulZi <pavel.zimakoff@gmail.com>
 * @license MIT (https://github.com/paulzi/yii2-sortable/blob/master/LICENSE)
 */

namespace paulzi\sortable\tests;

use paulzi\sortable\tests\models\Item;
use paulzi\sortable\tests\models\ItemWindow;
use Yii;

/**
 * @author PaulZi <pavel.zimakoff@gmail.com>
 */
class SortableBehaviorTestCase extends BaseTestCase
{
    public function testGetSortablePosition()
    {
        $this->assertEquals(-1, Item::findOne(9)->getSortablePosition());
        $this->assertEquals(-1, ItemWindow::findOne(9)->getSortablePosition());
    }

    public function testMoveFirstInsertInNoEmpty()
    {
        $item = new Item(['parent_id' => 1, 'slug' => 'new1']);
        $this->assertTrue($item->moveFirst()->save());

        $item = new ItemWindow(['parent_id' => 6, 'slug' => 'new2']);
        $this->assertTrue($item->moveFirst()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-first-insert-in-no-empty.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveFirstInsertInEmpty()
    {
        $item = new Item(['parent_id' => 4, 'slug' => 'new1']);
        $this->assertTrue($item->moveFirst()->save());

        $item = new ItemWindow(['parent_id' => 11, 'slug' => 'new2']);
        $this->assertTrue($item->moveFirst()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-first-insert-in-empty.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveFirstUpdateSameScope()
    {
        $item = Item::findOne(2);
        $this->assertTrue($item->moveFirst()->save());

        $item = ItemWindow::findOne(10);
        $this->assertTrue($item->moveFirst()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-first-update-same-scope.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveFirstUpdateOtherScope()
    {
        $item = Item::findOne(3);
        $item->parent_id = 6;
        $this->assertTrue($item->moveFirst()->save());

        $item = ItemWindow::findOne(12);
        $item->parent_id = 1;
        $this->assertTrue($item->moveFirst()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-first-update-other-scope.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveFirstUpdateSelf()
    {
        $item = Item::findOne(4);
        $this->assertTrue($item->moveFirst()->save());

        $item = ItemWindow::findOne(7);
        $this->assertTrue($item->moveFirst()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/data.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveLastInsertInNoEmpty()
    {
        $item = new Item(['parent_id' => 1, 'slug' => 'new1']);
        $this->assertTrue($item->moveLast()->save());

        $item = new ItemWindow(['parent_id' => 6, 'slug' => 'new2']);
        $this->assertTrue($item->moveLast()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-last-insert-in-no-empty.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveLastInsertInEmpty()
    {
        $item = new Item(['parent_id' => 4, 'slug' => 'new1']);
        $this->assertTrue($item->moveLast()->save());

        $item = new ItemWindow(['parent_id' => 11, 'slug' => 'new2']);
        $this->assertTrue($item->moveLast()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-last-insert-in-empty.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveLastUpdateSameScope()
    {
        $item = Item::findOne(3);
        $this->assertTrue($item->moveLast()->save());

        $item = ItemWindow::findOne(10);
        $this->assertTrue($item->moveLast()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-last-update-same-scope.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveLastUpdateOtherScope()
    {
        $item = Item::findOne(3);
        $item->parent_id = 6;
        $this->assertTrue($item->moveLast()->save());

        $item = ItemWindow::findOne(12);
        $item->parent_id = 1;
        $this->assertTrue($item->moveLast()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-last-update-other-scope.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveLastUpdateSelf()
    {
        $item = Item::findOne(5);
        $this->assertTrue($item->moveLast()->save());

        $item = ItemWindow::findOne(8);
        $this->assertTrue($item->moveLast()->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/data.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveToInsertNoGap()
    {
        $item = new Item(['parent_id' => 1, 'slug' => 'new1']);
        $this->assertTrue($item->moveTo(0, true)->save());

        $item = new ItemWindow(['parent_id' => 6, 'slug' => 'new2']);
        $this->assertTrue($item->moveTo(3, false)->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-to-insert-no-gap.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveToInsertGap()
    {
        $item = new Item(['parent_id' => 1, 'slug' => 'new1']);
        $this->assertTrue($item->moveTo(-2, true)->save());

        $item = new ItemWindow(['parent_id' => 6, 'slug' => 'new2']);
        $this->assertTrue($item->moveTo(4, false)->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-to-insert-gap.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveToUpdateSameScope()
    {
        $item = Item::findOne(3);
        $this->assertTrue($item->moveTo(2, false)->save());

        $item = ItemWindow::findOne(10);
        $this->assertTrue($item->moveTo(4, true)->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-to-update-same-scope.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveToUpdateOtherScope()
    {
        $item = Item::findOne(5);
        $item->parent_id = 6;
        $this->assertTrue($item->moveTo(-1, true)->save());

        $item = ItemWindow::findOne(7);
        $item->parent_id = 1;
        $this->assertTrue($item->moveTo(0, false)->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-to-update-other-scope.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveToUpdateSelf()
    {
        $item = Item::findOne(2);
        $this->assertTrue($item->moveTo(1, false)->save());

        $item = ItemWindow::findOne(13);
        $this->assertTrue($item->moveTo(2, true)->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/data.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveBefore()
    {
        $item = Item::findOne(7);
        $this->assertTrue($item->moveBefore(Item::findOne(10))->save());

        $item = ItemWindow::findOne(5);
        $this->assertTrue($item->moveBefore(Item::findOne(2))->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-before.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testMoveAfter()
    {
        $item = Item::findOne(4);
        $this->assertTrue($item->moveAfter(Item::findOne(3))->save());

        $item = ItemWindow::findOne(8);
        $this->assertTrue($item->moveAfter(Item::findOne(11))->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-move-after.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testReorder()
    {
        $item = Item::findOne(7);
        $this->assertEquals(8, $item->reorder(false));

        $item = ItemWindow::findOne(4);
        $this->assertEquals(4, $item->reorder(true));

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-reorder.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsert()
    {
        $item = new Item(['parent_id' => 1, 'slug' => 'new1']);
        $this->assertTrue($item->save());

        $item = new ItemWindow(['parent_id' => 6, 'slug' => 'new2']);
        $this->assertTrue($item->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/test-insert.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testUpdate()
    {
        $item = Item::findOne(4);
        $this->assertTrue($item->save());

        $item = ItemWindow::findOne(8);
        $this->assertTrue($item->save());

        $dataSet = $this->getConnection()->createDataSet(['item']);
        $expectedDataSet = new ArrayDataSet(require(__DIR__ . '/data/data.php'));
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }
}