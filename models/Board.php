<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "board".
 *
 * @property integer $id
 * @property string $name
 * @property string $baseUrl
 */
class Board extends ActiveRecord
{
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
            [['name', 'baseUrl'], 'required'],
            [['baseUrl'], 'string'],
            [['name'], 'string', 'max' => 255],
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
}
