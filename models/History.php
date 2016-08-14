<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "history".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $commited_at
 * @property integer $value
 *
 * @property Item $item
 */
class History extends \yii\db\ActiveRecord
{
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
            [['item_id', 'commited_at', 'value'], 'required'],
            [['item_id', 'commited_at', 'value'], 'integer'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::className(), 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'item_id' => Yii::t('app', 'Item ID'),
            'commited_at' => Yii::t('app', 'Commited At'),
            'value' => Yii::t('app', 'Value'),
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
     * @inheritdoc
     * @return HistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new HistoryQuery(get_called_class());
    }
}
