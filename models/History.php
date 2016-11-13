<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "history".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $item_id
 * @property integer $board_id
 * @property integer $user_id
 * @property integer $commited_at
 * @property integer $value
 *
 * @property Item $item
 * @property Board $board
 * @property User $user
 */
class History extends \yii\db\ActiveRecord
{
    const TYPE_ITEM_VALUE = 10;
    const TYPE_EVENT_TRIG = 20;
    const TYPE_BOARD_CONNECTION = 30;
    const TYPE_USER_ACTION = 40;
    const TYPE_USER_CONNECTION = 50;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'commited_at'], 'required'],
            [['type', 'item_id', 'board_id', 'event_id', 'user_id', 'commited_at'], 'integer'],
            [['value'], 'safe'],
            [['commited_at'], 'default', 'value' => time()],
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
            'event_id' => Yii::t('app', 'Event'),
            'board_id' => Yii::t('app', 'Плата'),
            'user_id' => Yii::t('app', 'Плата'),
            'item_id' => Yii::t('app', 'Устройство'),
            'commited_at' => Yii::t('app', 'Зафиксировано'),
            'value' => Yii::t('app', 'Значение'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id'])->inverseOf('histories');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBoard()
    {
        return $this->hasOne(Board::className(), ['id' => 'board_id'])->inverseOf('histories');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->inverseOf('histories');
    }

    /**
     * @inheritdoc
     * @return HistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new HistoryQuery(get_called_class());
    }

    /**
     * @return array
     */
    public static function getTypesArray()
    {
        return [
            self::TYPE_ITEM_VALUE => 'Значение',
            self::TYPE_EVENT_TRIG => 'Event',
            self::TYPE_BOARD_CONNECTION => 'Состояние платы',
            self::TYPE_USER_ACTION => 'Действия пользователя',
            self::TYPE_USER_CONNECTION => 'Состояние пользователя',
        ];
    }

    /**
     * @return mixed
     */
    public function getTypeLabel()
    {
        return self::getTypesArray()[$this->type];
    }
}
