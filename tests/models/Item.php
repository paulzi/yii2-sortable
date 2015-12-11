<?php
/**
 * @link https://github.com/paulzi/yii2-sortable
 * @copyright Copyright (c) 2015 PaulZi <pavel.zimakoff@gmail.com>
 * @license MIT (https://github.com/paulzi/yii2-sortable/blob/master/LICENSE)
 */

namespace paulzi\sortable\tests\models;

use paulzi\sortable\SortableBehavior;

/**
 * @author PaulZi <pavel.zimakoff@gmail.com>
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $sort
 * @property string $slug
 *
 *
 * @method static Item|null findOne() findOne($condition)
 *
 * @mixin SortableBehavior
 */
class Item extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%item}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => SortableBehavior::className(),
                'query' => ['parent_id'],
            ],
        ];
    }
}