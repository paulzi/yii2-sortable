<?php
/**
 * @link https://github.com/paulzi/yii2-sortable
 * @copyright Copyright (c) 2015 PaulZi <pavel.zimakoff@gmail.com>
 * @license MIT (https://github.com/paulzi/yii2-sortable/blob/master/LICENSE)
 */

namespace paulzi\sortable;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Sortable Trait
 * @author PaulZi <pavel.zimakoff@gmail.com>
 */
trait SortableTrait
{
    /**
     * @var SortableBehavior
     */
    private $_sortableBehavior;


    /**
     * @throws InvalidConfigException
     */
    private function getSortableBehavior()
    {
        if ($this->_sortableBehavior === null) {
            /** @var \yii\base\Component|self $this */
            foreach ($this->getBehaviors() as $behavior) {
                if ($behavior instanceof SortableBehavior) {
                    $this->_sortableBehavior = $behavior;
                }
            }
            if ($this->_sortableBehavior === null) {
                throw new InvalidConfigException('SortableBehavior is not attached to model');
            }
        }
        return $this->_sortableBehavior;
    }

    /**
     * @return integer
     */
    public function getSortablePosition()
    {
        return $this->getSortableBehavior()->getSortablePosition();
    }

    /**
     * @return $this
     */
    public function moveFirst()
    {
        return $this->getSortableBehavior()->moveFirst();
    }

    /**
     * @return $this
     */
    public function moveLast()
    {
        return $this->getSortableBehavior()->moveLast();
    }

    /**
     * @param integer $position
     * @param bool $forward Move existing items to forward or backward
     * @return $this
     */
    public function moveTo($position, $forward = true)
    {
        return $this->getSortableBehavior()->moveTo($position, $forward);
    }

    /**
     * @param ActiveRecord $model
     * @return $this
     */
    public function moveBefore($model)
    {
        return $this->getSortableBehavior()->moveBefore($model);
    }

    /**
     * @param ActiveRecord $model
     * @return $this
     */
    public function moveAfter($model)
    {
        return $this->getSortableBehavior()->moveAfter($model);
    }

    /**
     * Reorders items with values of sortAttribute begin from zero.
     * @param bool $middle
     * @return integer
     * @throws \Exception
     */
    public function reorder($middle = true)
    {
        return $this->getSortableBehavior()->reorder($middle);
    }
}