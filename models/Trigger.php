<?php

namespace app\models;

use voskobovich\linker\LinkerBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "trigger".
 *
 * @property integer $id
 * @property boolean $active
 * @property integer $type
 * @property integer $trig_date
 * @property string $trig_time
 * @property mixed $trig_time_wdays
 * @property integer $trig_item_id
 * @property string $trig_item_value
 * @property string $name
 *
 * @property Task[] $tasks
 */
class Trigger extends ActiveRecord
{
    const TYPE_BY_ITEM_VALUE = 10;
    const TYPE_BY_USER_ITEM_CHANGE = 20;
    const TYPE_BY_DATE = 30;
    const TYPE_BY_TIME = 40;
    const TYPE_MANUAL = 50;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'trigger';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'active'], 'required'],
            [['type', 'trig_item_id', 'trig_date'], 'integer'],
            [['type'], 'in', 'range' => self::getTypesArray()],
            [['trig_item_value', 'name', 'trig_time'], 'string', 'max' => 255],
            [['trig_time', 'trig_time_wdays', 'trig_item_value', 'trig_date'], 'default', 'value' => null],
            [['trig_time_wdays'], 'each', 'rule' => ['in', 'range' => self::getWeekDaysArray()]],
            [['task_ids'], 'each', 'rule' => ['integer']],
            [['active'], 'boolean'],
            [['active'], 'default', 'value' => true],
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
                    'task_ids' => 'tasks',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Включен',
            'type' => 'Тип',
            'trig_date' => 'Дата срабатывания',
            'trig_time' => 'Время срабатывания',
            'trig_time_wdays' => 'Дни срабатывания',
            'trig_item_id' => 'Элемент срабатывания',
            'trig_item_value' => 'Значение элемента срабатывания',
            'task_ids' => 'Задачи',
            'name' => 'Название',
        ];
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_BY_ITEM_VALUE => 'Изменение Значения Элемента',
            self::TYPE_BY_USER_ITEM_CHANGE => 'Изменение Пользователем Значения Элемента',
            self::TYPE_BY_DATE => 'Дата',
            self::TYPE_BY_TIME => 'Время',
            self::TYPE_MANUAL => 'Вручную (API)',
        ];
    }

    /**
     * @return array
     */
    public static function getWeekDays()
    {
        return [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье',
        ];
    }

    /**
     * @return array
     */
    public static function getWeekDaysArray()
    {
        return array_keys(self::getWeekDays());
    }

    /**
     * @return array
     */
    public static function getTypesArray()
    {
        return array_keys(self::getTypes());
    }

    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return self::getTypes()[$this->type];
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::className(), ['id' => 'task_id'])
            ->viaTable('trigger_task', ['trigger_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (is_array($this->trig_time_wdays)) {
                $this->trig_time_wdays = implode($this->trig_time_wdays, ', ');
            }

            return true;
        } else {
            return false;
        }
    }
}
