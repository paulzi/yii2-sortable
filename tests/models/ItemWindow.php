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
 * @method static ItemWindow|null findOne() findOne($condition)
 *
 * @mixin SortableBehavior
 */
class ItemWindow extends \yii\db\ActiveRecord
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
                'query' => function ($model) {
                    /** @var ItemWindow $model */
                    $tableName = $model->tableName();
                    return $model->find()->andWhere(["{$tableName}.[[parent_id]]" => $model->parent_id]);
                },
                'joinMode' => false,
            ],
        ];
    }
}