<?php

namespace app\models;

use voskobovich\linker\LinkerBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "trigger".
 *
 * @property integer $id
 * @property boolean $active
 * @property integer $type
 * @property integer $trig_date
 * @property string $trig_time
 * @property mixed $trig_time_wdays
 * @property integer $trig_item_id
 * @property string $trig_item_value
 * @property integer $trig_board_id
 * @property string $trig_connection_value
 * @property string $name
 *
 * @property Task[] $tasks
 * @property Item $item
 * @property Board $board
 */
class Trigger extends ActiveRecord
{
    const TYPE_BY_ITEM_VALUE = 10;
    const TYPE_BY_USER_ITEM_CHANGE = 20;
    const TYPE_BY_DATE = 30;
    const TYPE_BY_TIME = 40;
    const TYPE_MANUAL = 50;
    const TYPE_BOARD_CONNECTION = 60;

    const CONNECTION_VALUE_DISCONNECTED = 0;
    const CONNECTION_VALUE_CONNECTED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'trigger';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'active'], 'required'],
            [['type', 'trig_item_id', 'trig_board_id', 'trig_date'], 'integer'],
            [['type'], 'in', 'range' => self::getTypesArray()],
            [['trig_connection_value'], 'in', 'range' => self::getConnectionValuesArray()],
            [['trig_item_value', 'name', 'trig_time'], 'string', 'max' => 255],
            [['trig_time', 'trig_time_wdays', 'trig_item_value', 'trig_item_id', 'trig_board_id', 'trig_connection_value', 'trig_date'], 'default', 'value' => null],
            [['trig_time_wdays'], 'each', 'rule' => ['in', 'range' => self::getWeekDaysArray()]],
            [['task_ids'], 'each', 'rule' => ['integer']],
            [['active'], 'boolean'],
            [['active'], 'default', 'value' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'task_ids' => 'tasks',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Включен',
            'type' => 'Тип',
            'trig_date' => 'Дата срабатывания',
            'trig_time' => 'Время срабатывания',
            'trig_time_wdays' => 'Дни срабатывания',
            'trig_item_id' => 'Элемент срабатывания',
            'trig_item_value' => 'Значение элемента срабатывания',
            'task_ids' => 'Задачи',
            'trig_board_id' => 'Устройство',
            'trig_connection_value' => 'Значение состояния',
            'name' => 'Название',
        ];
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_BY_ITEM_VALUE => 'Изменение Значения Элемента',
            self::TYPE_BY_USER_ITEM_CHANGE => 'Изменение Пользователем Значения Элемента',
            self::TYPE_BY_DATE => 'Дата',
            self::TYPE_BY_TIME => 'Время',
            self::TYPE_MANUAL => 'Вручную (API)',
            self::TYPE_BOARD_CONNECTION => 'Состояние подключения платы',
        ];
    }

    /**
     * @return array
     */
    public static function getTypesArray()
    {
        return array_keys(self::getTypes());
    }

    /**
     * @return array
     */
    public static function getConnectionValues()
    {
        return [
            self::CONNECTION_VALUE_CONNECTED => 'Подключено',
            self::CONNECTION_VALUE_DISCONNECTED => 'Отключено',
        ];
    }

    /**
     * @return array
     */
    public static function getConnectionValuesArray()
    {
        return array_keys(self::getConnectionValues());
    }

    /**
     * @return array
     */
    public static function getWeekDays()
    {
        return [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье',
        ];
    }

    /**
     * @return array
     */
    public static function getWeekDaysArray()
    {
        return array_keys(self::getWeekDays());
    }

    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return self::getTypes()[$this->type];
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::className(), ['id' => 'task_id'])
            ->viaTable('trigger_task', ['trigger_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBoard()
    {
        return $this->hasOne(Board::className(), ['board_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (is_array($this->trig_time_wdays)) {
                $this->trig_time_wdays = implode($this->trig_time_wdays, ', ');
            }

            return true;
        } else {
            return false;
        }
    }
}
