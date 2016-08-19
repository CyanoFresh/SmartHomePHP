<?php

namespace app\models;

use linslin\yii2\curl\Curl;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $update_interval
 * @property integer $save_history_interval
 * @property integer $room_id
 * @property integer $board_id
 * @property string $url
 * @property string $name
 * @property string $icon
 * @property string $bg
 * @property string $class
 * @property integer $sort_order
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

    const VALUE_ON = 1;
    const VALUE_OFF = 0;

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
            [['type', 'update_interval', 'save_history_interval', 'room_id', 'url', 'name', 'icon', 'bg', 'board_id'], 'required'],
            [['type', 'update_interval', 'save_history_interval', 'room_id', 'sort_order', 'board_id'], 'integer'],
            [['url', 'name', 'icon', 'bg', 'class'], 'string', 'max' => 255],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::className(), 'targetAttribute' => ['room_id' => 'id']],
            [['board_id'], 'exist', 'skipOnError' => true, 'targetClass' => Board::className(), 'targetAttribute' => ['board_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Тип'),
            'update_interval' => Yii::t('app', 'Интервал обновления'),
            'save_history_interval' => Yii::t('app', 'Интервал сохранения в историю'),
            'room_id' => Yii::t('app', 'Комната'),
            'board_id' => Yii::t('app', 'Плата'),
            'url' => Yii::t('app', 'Url'),
            'name' => Yii::t('app', 'Название'),
            'icon' => Yii::t('app', 'Иконка'),
            'bg' => Yii::t('app', 'Фон'),
            'class' => Yii::t('app', 'Класс'),
            'sort_order' => Yii::t('app', 'Порядок сортировки'),
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

    public static function getTypesArray()
    {
        return [
            self::TYPE_SWITCH => 'Переключатель',
            self::TYPE_VARIABLE => 'Переменная',
            self::TYPE_VARIABLE_BOOLEAN => 'Переменная булев',
            self::TYPE_VARIABLE_BOOLEAN_DOOR => 'Переменная булев дверь',
        ];
    }
}
