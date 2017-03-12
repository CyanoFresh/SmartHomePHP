<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "room".
 *
 * @property integer $id
 * @property string $name
 * @property integer $sort_order
 *
 * @property Item[] $items
 */
class Room extends ActiveRecord
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
            [['name'], 'string', 'max' => 255],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
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
            'sort_order' => Yii::t('app', 'Порядок сортировки'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery|ItemWidgetQuery
     */
    public function getItemWidgets()
    {
        return $this->hasMany(ItemWidget::className(), ['room_id' => 'id'])->inverseOf('room');
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
