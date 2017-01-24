<?php

namespace app\models;

use voskobovich\linker\LinkerBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%event}}".
 *
 * @property integer $id
 * @property boolean $active
 * @property string $name
 * @property string $description
 * @property integer $last_triggered_at
 *
 * @property Task[] $tasks
 * @property Trigger[] $triggers
 */
class Event extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%event}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['active'], 'boolean'],
            [['active'], 'default', 'value' => true],
            [['description'], 'string'],
            [['last_triggered_at'], 'integer'],
            [['task_ids', 'trigger_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'active' => Yii::t('app', 'Активен'),
            'name' => Yii::t('app', 'Название'),
            'description' => Yii::t('app', 'Описание'),
            'last_triggered_at' => Yii::t('app', 'Дата Последней Активации'),
            'trigger_ids' => Yii::t('app', 'Триггеры'),
            'task_ids' => Yii::t('app', 'Задачи'),
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
                    'task_ids' => 'tasks',
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::className(), ['id' => 'task_id'])->viaTable('{{%event_task}}', ['event_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTriggers()
    {
        return $this->hasMany(Trigger::className(), ['id' => 'trigger_id'])->viaTable('{{%event_trigger}}', ['event_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
