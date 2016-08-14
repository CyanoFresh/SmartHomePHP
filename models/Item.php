<?php

namespace app\models;

use linslin\yii2\curl\Curl;
use Yii;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $update_interval
 * @property integer $save_history_interval
 * @property integer $room_id
 * @property string $url
 * @property string $name
 * @property integer $sort_order
 *
 * @property History[] $histories
 * @property Room $room
 */
class Item extends \yii\db\ActiveRecord
{
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
            [['type', 'update_interval', 'save_history_interval', 'room_id', 'url', 'name', 'sort_order'], 'required'],
            [['type', 'update_interval', 'save_history_interval', 'room_id', 'sort_order'], 'integer'],
            [['url', 'name'], 'string', 'max' => 255],
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
            'type' => Yii::t('app', 'Type'),
            'update_interval' => Yii::t('app', 'Update Interval'),
            'save_history_interval' => Yii::t('app', 'Save History Interval'),
            'room_id' => Yii::t('app', 'Room ID'),
            'url' => Yii::t('app', 'Url'),
            'name' => Yii::t('app', 'Name'),
            'sort_order' => Yii::t('app', 'Sort Order'),
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
     * @inheritdoc
     * @return ItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemQuery(get_called_class());
    }

    public function getValueViaApi()
    {
        $curl = new Curl();
    }
}
