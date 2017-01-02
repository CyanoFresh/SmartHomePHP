<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "room".
 *
 * @property integer $id
 * @property string $name
 * @property string $bg
 *
 * @property Item[] $items
 */
class Room extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'bg'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Название'),
            'bg' => Yii::t('app', 'Фон'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['room_id' => 'id'])->inverseOf('room');
    }

    /**
     * @inheritdoc
     * @return RoomQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RoomQuery(get_called_class());
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
