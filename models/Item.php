<?php

namespace app\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property boolean $active
 * @property integer $type
 * @property integer $update_interval
 * @property boolean $enable_log
 * @property integer $save_history_interval
 * @property integer $room_id
 * @property integer $board_id
 * @property integer $pin
 * @property string $url
 * @property string $name
 * @property string $icon
 * @property string $bg
 * @property string $class
 * @property integer $sort_order
 * @property string $default_value
 *
 * @property History[] $histories
 * @property Room $room
 * @property Board $board
 */
class Item extends ActiveRecord
{
    const TYPE_SWITCH = 10;
    const TYPE_VARIABLE = 20;
    const TYPE_VARIABLE_BOOLEAN = 25;
    const TYPE_VARIABLE_BOOLEAN_DOOR = 26;
    const TYPE_VARIABLE_TEMPERATURE = 21;
    const TYPE_VARIABLE_HUMIDITY = 22;
    const TYPE_RGB = 30;

    const VALUE_ON = 1;
    const VALUE_OFF = 0;

    const MODE_RAINBOW = 'rainbow';
    const MODE_BREATH = 'breath';

    /**
     * Used for WS handler
     * @var mixed
     */
    public $value;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['active', 'type', 'room_id', 'name', 'icon', 'bg', 'board_id'], 'required'],
            [
                ['type', 'update_interval', 'save_history_interval', 'room_id', 'sort_order', 'board_id', 'pin'],
                'integer'
            ],
            [['url', 'name', 'icon', 'bg', 'class', 'default_value'], 'string', 'max' => 255],
            [
                ['room_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Room::className(),
                'targetAttribute' => ['room_id' => 'id']
            ],
            [
                ['board_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Board::className(),
                'targetAttribute' => ['board_id' => 'id']
            ],
            [['sort_order', 'update_interval', 'save_history_interval'], 'default', 'value' => 0],
            [['active', 'enable_log'], 'default', 'value' => true],
            [['active', 'enable_log'], 'boolean'],
            [['default_value'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'active' => Yii::t('app', 'Активно'),
            'enable_log' => Yii::t('app', 'Логирование'),
            'type' => Yii::t('app', 'Тип'),
            'update_interval' => Yii::t('app', 'Интервал обновления'),
            'save_history_interval' => Yii::t('app', 'Интервал сохранения в историю'),
            'room_id' => Yii::t('app', 'Комната'),
            'board_id' => Yii::t('app', 'Плата'),
            'url' => Yii::t('app', 'Url'),
            'pin' => Yii::t('app', 'Pin'),
            'name' => Yii::t('app', 'Название'),
            'icon' => Yii::t('app', 'Иконка'),
            'bg' => Yii::t('app', 'CSS Background'),
            'class' => Yii::t('app', 'CSS Класс'),
            'sort_order' => Yii::t('app', 'Порядок сортировки'),
            'default_value' => Yii::t('app', 'Значение по умолчанию'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistories()
    {
        return $this->hasMany(History::className(), ['item_id' => 'id'])->inverseOf('item');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'room_id'])->inverseOf('items');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBoard()
    {
        return $this->hasOne(Board::className(), ['id' => 'board_id'])->inverseOf('items');
    }

    /**
     * @inheritdoc
     * @return ItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemQuery(get_called_class());
    }

    /**
     * @return array
     */
    public static function getTypesArray()
    {
        return [
            self::TYPE_SWITCH => 'Переключатель',
            self::TYPE_VARIABLE => 'Переменная',
            self::TYPE_VARIABLE_BOOLEAN => 'Переменная булев',
            self::TYPE_VARIABLE_BOOLEAN_DOOR => 'Переменная булев дверь',
            self::TYPE_VARIABLE_TEMPERATURE => 'Переменная температура',
            self::TYPE_VARIABLE_HUMIDITY => 'Переменная влажность',
            self::TYPE_RGB => 'RGB',
        ];
    }

    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return self::getTypesArray()[$this->type];
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return array
     */
    public static function getModesArray()
    {
        return [
            self::MODE_RAINBOW,
            self::MODE_BREATH,
        ];
    }

    /**
     * Returns normalized default value
     * @return mixed
     */
    public function getDefaultValue()
    {
        if (!is_null($this->default_value)) {
            return $this->default_value;
        }

        switch ($this->type) {
            case Item::TYPE_SWITCH:
            case Item::TYPE_VARIABLE_BOOLEAN:
            case Item::TYPE_VARIABLE_BOOLEAN_DOOR:
                return false;

            case Item::TYPE_VARIABLE_TEMPERATURE:
            case Item::TYPE_VARIABLE_HUMIDITY:
                return 0;

            case Item::TYPE_RGB:
                return [
                    0,
                    0,
                    0,
                ];
        }

        return false;
    }
}
