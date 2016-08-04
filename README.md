# Yii2 Sortable Behavior

It implements the ability to control the order of the ActiveRecord.

[![Packagist Version](https://img.shields.io/packagist/v/paulzi/yii2-sortable.svg)](https://packagist.org/packages/paulzi/yii2-sortable)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/paulzi/yii2-sortable/master.svg)](https://scrutinizer-ci.com/g/paulzi/yii2-sortable/?branch=master)
[![Build Status](https://img.shields.io/travis/paulzi/yii2-sortable/master.svg)](https://travis-ci.org/paulzi/yii2-sortable)
[![Total Downloads](https://img.shields.io/packagist/dt/paulzi/yii2-sortable.svg)](https://packagist.org/packages/paulzi/yii2-sortable)

## Install

Install via Composer:

```bash
composer require paulzi/yii2-sortable
```

or add

```bash
"paulzi/yii2-sortable" : "^1.0"
```

to the `require` section of your `composer.json` file.

## Migrations

Add signed integer column to your model.
For quick operation behavior, add index for scopes fields and sort attribute, example:
```php
$this->createIndex('parent_sort', '{{%item}}', ['parent_id', 'sort']);
```

## Configuring

```php
use paulzi\sortable\SortableBehavior;

class Sample extends \yii\db\ActiveRecord
{
    public function behaviors() {
        return [
            [
                'class' => SortableBehavior::className(),
            ],
        ];
    }
}
```

## Options

- `$query = null` - list of attributes, callback or ActiveQuery, to find scope element. See below.
- `$sortAttribute = 'sort'` - sort attribute in table schema.
- `$step = 100` - gap size between elements. 
- `$joinMode = true` - search method of unallocated value. When joinMode is true, using join table with self. Otherwise, use the search in the window. Window size defined by $windowSize property.
- `$windowSize = 1000` - defines the size of the search window, when joinMode is false.

**Details of `$query` option:**
The list of attributes - a simple way to scope elements with the same content fields, the aliases do not need.
You MUST use tableName() alias in ActiveQuery, when you are using joinMode.

For example,
```php
public function behaviors()
{
    return [
        [
            'class' => SortableBehavior::className(),
            'query' => ['parent_id'],
        ]
    ];
}
```

This is equivalent to:
```php
public function behaviors()
{
    return [
        [
            'class' => SortableBehavior::className(),
            'query' => function ($model) {
                $tableName = $model->tableName();
                return $model->find()->andWhere(["{$tableName}.[[parent_id]]" => $model->parent_id]);
            },
        ]
    ];
}
```

## Usage

Getting sort attribute value:

```php
$model = Sample::findOne(1);
$position = $model->getSortablePosition();
```

To move as the first item:

```php
$model = new Sample(['parent_id' => 1]);
$model->moveFirst()->save(); // inserting new node
```

To move as the last item:

```php
$model = Sample::findOne(1);
$model->moveLast()->save(); // move existing node
```

To move an item to a specific position:

```php
$model = Sample::findOne(1);
$model->moveTo(-33, true)->save(); // move to position -33, and move existing items forward
$model = Sample::findOne(2);
$model->moveTo(4, false)->save(); // move to position 4, and move existing items backward
```

To move an item before another:
*Note: If you need to change scope, do it manually*

```php
$model1 = new Sample(['parent_id' => 1]);
$model2 = Sample::findOne(2);
$model1->moveBefore($model2)->save(); // move $model1 before $model2
```

To move an item after another:
*Note: If you need to change scope, do it manually*

```php
$model1 = Sample::findOne(1);
$model2 = Sample::findOne(2);
$model1->parent_id = $model2->parent_id;
$model1->moveAfter($model2)->save(); // move $model1 after $model2 with change scope
```

Reorder item with the neighboring elements:

```php
$model = Sample::findOne(1);
$model->reorder(true); // reorder with center zero
$model = Sample::findOne(2);
$model->reorder(false); // reorder from zero
```

## SortableTrait

You can use optional `SortableTrait`, if you need it (for example, you are using something like `ISortable` interface).