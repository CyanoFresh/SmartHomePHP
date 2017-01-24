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
 * @property integer $date
 * @property string $time
 * @property mixed $weekdays
 * @property integer $item_id
 * @property string $item_value
 * @property integer $board_id
 * @property string $connection_value
 * @property string $name
 *
 * @property Item $item
 * @property Board $board
 * @property Event[] $events
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
            [['type', 'item_id', 'board_id', 'date'], 'integer'],
            [['type'], 'in', 'range' => self::getTypesArray()],
            [['connection_value'], 'in', 'range' => self::getConnectionValuesArray()],
            [['item_value', 'name', 'time'], 'string', 'max' => 255],
            [['time', 'weekdays', 'item_value', 'item_id', 'board_id', 'connection_value', 'date'], 'default', 'value' => null],
            [['weekdays'], 'each', 'rule' => ['in', 'range' => self::getWeekDaysArray()]],
            [['active'], 'boolean'],
            [['active'], 'default', 'value' => true],
            [['event_ids'], 'each', 'rule' => ['integer']],
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
                    'event_ids' => 'events',
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
            'date' => 'Дата',
            'time' => 'Время',
            'weekdays' => 'Дни недели',
            'item_id' => 'Элемент',
            'item_value' => 'Значение элемента',
            'board_id' => 'Устройство',
            'connection_value' => 'Значение состояния',
            'event_ids' => 'События',
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
            self::TYPE_MANUAL => 'Вручную',
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
     * @return \yii\db\ActiveQuery
     */
    public function getEvents()
    {
        return $this->hasMany(Event::className(), ['id' => 'event_id'])->viaTable('{{%event_trigger}}', ['trigger_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (is_array($this->weekdays)) {
                $this->weekdays = implode($this->weekdays, ', ');
            }

            return true;
        } else {
            return false;
        }
    }
}
