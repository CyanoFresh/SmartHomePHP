<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "board".
 *
 * @property integer $id
 * @property integer $type
 * @property string $name
 * @property string $secret
 * @property string $baseUrl
 *
 * @property Item[] $items
 */
class Board extends ActiveRecord
{
    const TYPE_AREST = 10;
    const TYPE_WEBSOCKET = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'board';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['type'], 'integer'],
            [['baseUrl'], 'string'],
            [['name', 'secret'], 'string', 'max' => 255],
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
            'name' => Yii::t('app', 'Название'),
            'secret' => Yii::t('app', 'Ключ'),
            'baseUrl' => Yii::t('app', 'Base Url'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['board_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return BoardQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BoardQuery(get_called_class());
    }

    /**
     * @return array
     */
    public static function getTypesArray()
    {
        return [
            self::TYPE_AREST => 'aREST API',
            self::TYPE_WEBSOCKET => 'WebSocket API',
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
     * @return \yii\db\ActiveQuery
     */
    public function getHistories()
    {
        return $this->hasMany(History::className(), ['board_id' => 'id'])->inverseOf('board');
    }
}
