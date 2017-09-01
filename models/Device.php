<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "device".
 *
 * @property integer $id
 * @property string $type
 * @property integer $room_id
 * @property string $name
 * @property string $title
 * @property string $key
 * @property integer $allow_remote_connection
 *
 * @property Room $room
 */
class Device extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'device';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'title', 'key'], 'required'],
            [['room_id', 'allow_remote_connection'], 'integer'],
            [['type', 'name'], 'string', 'max' => 100],
            [['title', 'key'], 'string', 'max' => 255],
            [['key'], 'unique'],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::className(), 'targetAttribute' => ['room_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'room_id' => 'Room ID',
            'name' => 'Name',
            'title' => 'Title',
            'key' => 'Key',
            'allow_remote_connection' => 'Allow Remote Connection',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'room_id'])->inverseOf('devices');
    }

    /**
     * @inheritdoc
     * @return \app\models\query\DeviceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\DeviceQuery(get_called_class());
    }
}
