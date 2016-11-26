<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "task_action".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $item_id
 * @property string $item_value
 * @property integer $task_id
 * @property string $name
 *
 * @property Item $item
 */
class TaskAction extends \yii\db\ActiveRecord
{
    const TYPE_CHANGE_ITEM_VALUE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task_action';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['type', 'item_id', 'task_id'], 'integer'],
            [['item_value', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип',
            'item_id' => 'Элемент',
            'item_value' => 'Значение Элемента',
            'task_id' => 'Задача',
            'name' => 'Название',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }

    public static function getTypes()
    {
        return [
            self::TYPE_CHANGE_ITEM_VALUE => 'Изменить значение Элемента',
        ];
    }
}
