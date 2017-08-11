<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "item_widget".
 *
 * @property integer $id
 * @property boolean $active
 * @property integer $type
 * @property integer $value_type
 * @property integer $sort_order
 * @property integer $item_id
 * @property integer $room_id
 * @property string $name
 * @property string $html_class
 * @property string $icon
 *
 * @property Item $item
 * @property Room $room
 */
class ItemWidget extends ActiveRecord
{
    const TYPE_SWITCH = 10;
    const TYPE_VARIABLE = 20;
    const TYPE_RGB = 30;
    const TYPE_PLANT = 40;

    const VALUE_TYPE_BOOLEAN = 10;
    const VALUE_TYPE_DOOR = 20;
    const VALUE_TYPE_CELSIUS = 30;
    const VALUE_TYPE_PERCENT = 40;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item_widget';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort_order', 'item_id', 'room_id', 'type', 'value_type'], 'integer'],
            [['active'], 'boolean'],
            [['active'], 'default', 'value' => true],
            [['type'], 'in', 'range' => self::getTypesArray()],
            [['value_type'], 'in', 'range' => self::getValueTypesArray()],
            [['name', 'item_id', 'icon', 'type'], 'required'],
            [['name', 'html_class', 'icon'], 'string', 'max' => 255],
            [['sort_order'], 'default', 'value' => 0],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::className(), 'targetAttribute' => ['room_id' => 'id']],
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
            'type' => Yii::t('app', 'Тип'),
            'value_type' => Yii::t('app', 'Тип Значения'),
            'sort_order' => Yii::t('app', 'Порядок сортировки'),
            'item_id' => Yii::t('app', 'Элемент'),
            'room_id' => Yii::t('app', 'Комната'),
            'name' => Yii::t('app', 'Название'),
            'html_class' => Yii::t('app', 'HTML класс'),
            'icon' => Yii::t('app', 'Иконка'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id'])->inverseOf('widget');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'room_id'])->inverseOf('itemWidgets');
    }

    /**
     * @inheritdoc
     * @return ItemWidgetQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemWidgetQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function getName()
    {
        if ($this->name != null) {
            return $this->name;
        }

        return $this->item->name;
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_SWITCH => 'Переключатель',
            self::TYPE_VARIABLE => 'Переменная',
            self::TYPE_RGB => 'RGB',
            self::TYPE_PLANT => 'Растение',
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
    public static function getValueTypes()
    {
        return [
            self::VALUE_TYPE_BOOLEAN => 'Boolean',
            self::VALUE_TYPE_DOOR => 'Дверь',
            self::VALUE_TYPE_CELSIUS => 'Цельсии',
            self::VALUE_TYPE_PERCENT => 'Проценты',
        ];
    }

    /**
     * @return array
     */
    public static function getValueTypesArray()
    {
        return array_keys(self::getValueTypes());
    }
}
