<?php
/**
 * @link https://github.com/paulzi/yii2-sortable
 * @copyright Copyright (c) 2015 PaulZi <pavel.zimakoff@gmail.com>
 * @license MIT (https://github.com/paulzi/yii2-sortable/blob/master/LICENSE)
 */

namespace paulzi\sortable;

use yii\base\Behavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;


/**
 * Sortable Behavior for Yii2
 * @author PaulZi <pavel.zimakoff@gmail.com>
 *
 * @property ActiveRecord $owner
 */
class SortableBehavior extends Behavior
{
    const OPERATION_FIRST             = 1;
    const OPERATION_LAST              = 2;
    const OPERATION_POSITION_BACKWARD = 3;
    const OPERATION_POSITION_FORWARD  = 4;

    /**
     * List of attributes, callable or ActiveQuery
     * The list of attributes - a simple way to scope elements with the same content fields, the aliases do not need.
     * Warning! You MUST use tableName() alias in ActiveQuery, when you are using joinMode:
     * For example,
     *
     * ~~~
     * public function behaviors()
     * {
     *     return [
     *         [
     *             'class' => SortableBehavior::className(),
     *             'query' => ['parent_id'],
     *         ]
     *     ];
     * }
     * ~~~
     *
     * This is equivalent to:
     *
     * ~~~
     * public function behaviors()
     * {
     *     return [
     *         [
     *             'class' => SortableBehavior::className(),
     *             'query' => function ($model) {
     *                 $tableName = $model->tableName();
     *                 return $model->find()->andWhere(["{$tableName}.[[parent_id]]" => $model->parent_id]);
     *             },
     *         ]
     *     ];
     * }
     * ~~~
     *
     * @var array|callable|ActiveQuery
     */
    public $query;

    /**
     * @var string
     */
    public $sortAttribute = 'sort';

    /**
     * @var int
     */
    public $step = 100;

    /**
     * Search method of unallocated value.
     * When joinMode is true, using join table with self. Otherwise, use the search in the window. Window size defined by $windowSize property.
     * @var bool
     */
    public $joinMode = true;

    /**
     * Defines the size of the search window, when joinMode is false.
     * @var int
     */
    public $windowSize = 1000;

    /**
     * @var integer|null
     */
    protected $operation;

    /**
     * @var integer
     */
    protected $position;


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT   => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT    => 'afterSave',
            ActiveRecord::EVENT_BEFORE_UPDATE   => 'beforeSave',
            ActiveRecord::EVENT_AFTER_UPDATE    => 'afterSave',
        ];
    }

    /**
     * @return integer
     */
    public function getSortablePosition()
    {
        return $this->owner->getAttribute($this->sortAttribute);
    }

    /**
     * @return ActiveRecord
     */
    public function moveFirst()
    {
        $this->operation = self::OPERATION_FIRST;
        return $this->owner;
    }

    /**
     * @return ActiveRecord
     */
    public function moveLast()
    {
        $this->operation = self::OPERATION_LAST;
        return $this->owner;
    }

    /**
     * @param integer $position
     * @param bool $forward Move existing items to forward or backward
     * @return ActiveRecord
     */
    public function moveTo($position, $forward = true)
    {
        $this->operation = $forward ? self::OPERATION_POSITION_FORWARD : self::OPERATION_POSITION_BACKWARD;
        $this->position  = (int)$position;
        return $this->owner;
    }

    /**
     * @param ActiveRecord $model
     * @return ActiveRecord
     */
    public function moveBefore($model)
    {
        return $this->moveTo($model->getAttribute($this->sortAttribute) - 1, false);
    }

    /**
     * @param ActiveRecord $model
     * @return ActiveRecord
     */
    public function moveAfter($model)
    {
        return $this->moveTo($model->getAttribute($this->sortAttribute) + 1, true);
    }

    /**
     * Reorders items with values of sortAttribute begin from zero.
     * @param bool $middle
     * @return integer
     * @throws \Exception
     */
    public function reorder($middle = true)
    {
        $result = 0;
        \Yii::$app->getDb()->transaction(function () use (&$result, $middle) {
            $list = $this->getQueryInternal()
                ->select($this->owner->primaryKey())
                ->orderBy([$this->sortAttribute => SORT_ASC])
                ->asArray()
                ->all();
            $from = $middle ? count($list) >> 1 : 0;
            foreach ($list as $i => $item) {
                $result += $this->owner->updateAll([$this->sortAttribute => ($i - $from) * $this->step], $item);
            }
        });

        return $result;
    }

    /**
     *
     */
    public function beforeSave()
    {
        if ($this->owner->getIsNewRecord() && $this->operation === null) {
            $this->operation = self::OPERATION_LAST;
        }

        switch ($this->operation) {
            case self::OPERATION_FIRST:
            case self::OPERATION_LAST:
                $query = $this->getQueryInternal();
                $query->orderBy(null);
                $position = $this->operation === self::OPERATION_LAST ? $query->max($this->sortAttribute) : $query->min($this->sortAttribute);

                $isSelf = false;
                if ($position !== null && !$this->owner->getIsNewRecord() && (int)$position === $this->owner->getAttribute($this->sortAttribute)) {
                    if ($this->query instanceof ActiveQuery || is_callable($this->query)) {
                        $isSelf = $this->getQueryInternal()
                            ->andWhere([$this->sortAttribute => $position])
                            ->andWhere($this->selfCondition())
                            ->exists();

                    } else {
                        $isSelf = count($this->owner->getDirtyAttributes($this->query)) === 0;
                    }
                }

                if ($position === null) {
                    $this->owner->setAttribute($this->sortAttribute, 0);
                } elseif (!$isSelf) {
                    if ($this->operation === self::OPERATION_LAST) {
                        $this->owner->setAttribute($this->sortAttribute, $position + $this->step);
                    } else {
                        $this->owner->setAttribute($this->sortAttribute, $position - $this->step);
                    }
                }
                break;

            case self::OPERATION_POSITION_BACKWARD:
            case self::OPERATION_POSITION_FORWARD:
                $this->moveToInternal($this->position, $this->operation === self::OPERATION_POSITION_FORWARD);
                break;
        }
    }

    /**
     *
     */
    public function afterSave()
    {
        $this->operation = null;
    }

    /**
     * @return ActiveQuery
     */
    protected function getQueryInternal()
    {
        if ($this->query instanceof ActiveQuery) {
            $query = clone $this->query;
            return $query;
        } elseif (is_callable($this->query)) {
            return call_user_func($this->query, $this->owner);
        } else {
            $tableName  = $this->owner->tableName();
            $attributes = $this->owner->getAttributes($this->query);
            $attributes = array_combine(
                array_map(function ($value) use ($tableName) { return "{$tableName}.[[{$value}]]"; }, array_keys($attributes)),
                array_values($attributes)
            );
            return $this->owner->find()->andWhere($attributes);
        }
    }

    /**
     * @param string $tableName
     * @param string $string
     * @return string
     */
    protected static function getJoinConditionReplace($tableName, $string)
    {
        return str_replace($tableName . '.', 'n.', $string);
    }

    /**
     * @param string $tableName
     * @param array|string $condition
     * @return array|string
     */
    protected static function getJoinCondition($tableName, $condition)
    {
        if (is_string($condition)) {
            return static::getJoinConditionReplace($tableName, $condition);
        } elseif (is_array($condition)) {
            $joinCondition = [];
            array_walk($condition, function ($value, $key) use ($tableName, &$joinCondition) {
                $joinCondition[static::getJoinConditionReplace($tableName, $key)] = static::getJoinCondition($tableName, $value);
            });
            return $joinCondition;
        } elseif ($condition instanceof Expression) {
            $condition->expression = static::getJoinConditionReplace($tableName, $condition->expression);
        }
        return $condition;
    }

    /**
     * @return array
     */
    protected function selfCondition()
    {
        $tableName = $this->owner->tableName();
        $result = [];
        foreach ($this->owner->getPrimaryKey(true) as $field => $value) {
            $result["{$tableName}.[[{$field}]]"] = $value;
        }
        return $result;
    }

    /**
     * @param integer $from
     * @param integer|null $to
     * @param bool $forward
     */
    protected function shift($from, $to, $forward)
    {
        $query = $this->getQueryInternal();
        if ($to === null) {
            $condition = [$forward ? '>=' : '<=', $this->sortAttribute, $from];
        } else {
            $condition = ['between', $this->sortAttribute, $forward ? $from : $to, $forward ? $to : $from];
        }
        $this->owner->updateAll(
            [$this->sortAttribute => new Expression("[[{$this->sortAttribute}]] " . ($forward ? '+ 1' : '- 1'))],
            [
                'and',
                $query->where,
                $condition,
            ]
        );
    }

    /**
     * @param integer $position
     * @param bool $forward
     */
    protected function moveToInternal($position, $forward)
    {
        if ($this->joinMode) {
            $this->moveToInternalJoinMode($position, $forward);
        } else {
            $this->moveToInternalWindowMode($position, $forward);
        }
    }

    /**
     * @param integer $position
     * @param bool $forward
     */
    protected function moveToInternalJoinMode($position, $forward)
    {
        $this->owner->setAttribute($this->sortAttribute, $position);

        $tableName = $this->owner->tableName();
        $query     = $this->getQueryInternal();
        $joinCondition = [
            'and',
            static::getJoinCondition($tableName, $query->where),
            ["n.[[{$this->sortAttribute}]]" => new Expression("{$tableName}.[[{$this->sortAttribute}]] " . ($forward ? '+ 1' : '- 1'))],
        ];
        if (!$this->owner->getIsNewRecord()) {
            $joinCondition[] = ['not', static::getJoinCondition($tableName, $this->selfCondition())];
        }

        $exists = $query
            ->andWhere(["{$tableName}.[[{$this->sortAttribute}]]" => $position])
            ->andWhere(['not', $this->selfCondition()])
            ->exists();
        if ($exists) {
            $unallocated = $this->getQueryInternal()
                ->select("{$tableName}.[[{$this->sortAttribute}]]")
                ->leftJoin("{$tableName} n", $joinCondition)
                ->andWhere([
                    'and',
                    [$forward ? '>=' : '<=', "{$tableName}.[[{$this->sortAttribute}]]", $position - ($forward ? 1 : -1)],
                    ["n.[[{$this->sortAttribute}]]" => null],
                ])
                ->orderBy(["{$tableName}.[[{$this->sortAttribute}]]" => $forward ? SORT_ASC : SORT_DESC])
                ->limit(1)
                ->scalar();
            $this->shift($position, $unallocated, $forward);
        }
    }

    /**
     * @param integer $position
     * @param bool $forward
     */
    protected function moveToInternalWindowMode($position, $forward)
    {
        $this->owner->setAttribute($this->sortAttribute, $position);

        $tableName  = $this->owner->tableName();
        $query      = $this->getQueryInternal();
        if (!$this->owner->getIsNewRecord()) {
            $query->andWhere(['not', $this->selfCondition()]);
        }

        $list = $query
            ->select("{$tableName}.[[{$this->sortAttribute}]]")
            ->andWhere([$forward ? '>=' : '<=', "{$tableName}.[[{$this->sortAttribute}]]", $position])
            ->orderBy(["{$tableName}.[[{$this->sortAttribute}]]" => $forward ? SORT_ASC : SORT_DESC])
            ->limit($this->windowSize)
            ->column();
        $unallocated = null;
        $prev = $position - ($forward ? 1 : -1);
        foreach ($list as $item) {
            if (abs($item - $prev) > 1) {
                $unallocated = $prev;
                break;
            }
            $prev = $item;
        }

        $this->shift($position, $unallocated, $forward);
    }
}
