<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "event".
 *
 * @property integer $id
 * @property integer $type
 * @property string $trig_date
 * @property string $trig_time
 * @property string $trig_time_wdays
 * @property integer $trig_item_id
 * @property string $trig_item_value
 * @property integer $task_id
 * @property string $name
 *
 * @property Task $task
 */
class Event extends ActiveRecord
{
    const TYPE_BY_ITEM_VALUE = 10;
    const TYPE_BY_USER_ITEM_CHANGE = 20;
    const TYPE_BY_DATE = 30;
    const TYPE_BY_TIME = 40;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'event';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['type', 'trig_item_id', 'task_id'], 'integer'],
            [['type'], 'in', 'range' => self::getStatusesArray()],
            [['trig_date', 'trig_item_value', 'name', 'trig_time', 'trig_time_wdays'], 'string', 'max' => 255],
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
            'trig_date' => 'Дата срабатывания',
            'trig_time' => 'Время срабатывания',
            'trig_time_wdays' => 'Дни срабатывания',
            'trig_item_id' => 'Элемент срабатывания',
            'trig_item_value' => 'Значение элемента срабатывания',
            'task_id' => 'Задача',
            'name' => 'Имя',
        ];
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::TYPE_BY_ITEM_VALUE => 'Значение Элемента',
            self::TYPE_BY_USER_ITEM_CHANGE => 'Изменение Значение Элемента',
            self::TYPE_BY_DATE => 'Дата',
            self::TYPE_BY_TIME => 'Время',
        ];
    }

    /**
     * @return array
     */
    public static function getStatusesArray()
    {
        return [
            self::TYPE_BY_ITEM_VALUE,
            self::TYPE_BY_USER_ITEM_CHANGE,
            self::TYPE_BY_DATE,
            self::TYPE_BY_TIME,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }

    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
