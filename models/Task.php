<?php

namespace app\models;

use voskobovich\linker\LinkerBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "task".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $item_id
 * @property string $item_value
 * @property string $name
 *
 * @property Item $item
 * @property Trigger[] $triggers
 */
class Task extends ActiveRecord
{
    const TYPE_ITEM_VALUE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'item_id'], 'integer'],
            [['item_value', 'name'], 'string', 'max' => 255],
            [['trigger_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип',
            'item_id' => 'Элемент',
            'item_value' => 'Значение Элемента',
            'trigger_ids' => 'Triggers',
            'name' => 'Название',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => LinkerBehavior::className(),
                'relations' => [
                    'trigger_ids' => 'triggers',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ITEM_VALUE => 'Изменить значение Элемента',
        ];
    }

    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return self::getTypes()[$this->type];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTriggers()
    {
        return $this->hasMany(Trigger::className(), ['id' => 'trigger_id'])
            ->viaTable('trigger_task', ['task_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }
}
