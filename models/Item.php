<?php

namespace app\models;

use app\models\query\ItemQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $update_interval
 * @property boolean $enable_log
 * @property integer $save_history_interval
 * @property integer $board_id
 * @property integer $pin
 * @property string $url
 * @property string $name
 * @property string $default_value
 *
 * @property History[] $histories
 * @property Board $board
 * @property ItemWidget $widget
 */
class Item extends ActiveRecord
{
    const TYPE_SWITCH = 10;
    const TYPE_VARIABLE = 20;
    const TYPE_VARIABLE_BOOLEAN = 25;
    const TYPE_VARIABLE_BOOLEAN_DOOR = 26;
    const TYPE_VARIABLE_TEMPERATURE = 21;
    const TYPE_VARIABLE_HUMIDITY = 22;
    const TYPE_VARIABLE_LIGHT = 23;
    const TYPE_RGB = 30;
    const TYPE_PLANT = 40;

    const VALUE_ON = 1;
    const VALUE_OFF = 0;

    const RGB_MODE_STATIC = 'static';
    const RGB_MODE_WAVE = 'wave';
    const RGB_MODE_FADE = 'fade';

    /**
     * Used for WS handler
     * @var mixed
     */
    public $value;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'board_id'], 'required'],
            [
                ['type', 'update_interval', 'save_history_interval', 'board_id', 'pin'],
                'integer'
            ],
            [['url', 'name', 'default_value'], 'string', 'max' => 255],
            [
                ['board_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Board::className(),
                'targetAttribute' => ['board_id' => 'id']
            ],
            [['update_interval', 'save_history_interval'], 'default', 'value' => 0],
            [['default_value'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'enable_log' => Yii::t('app', 'Логирование'),
            'type' => Yii::t('app', 'Тип'),
            'update_interval' => Yii::t('app', 'Интервал обновления'),
            'save_history_interval' => Yii::t('app', 'Интервал сохранения в историю'),
            'board_id' => Yii::t('app', 'Плата'),
            'url' => Yii::t('app', 'Url'),
            'pin' => Yii::t('app', 'Pin'),
            'name' => Yii::t('app', 'Название'),
            'default_value' => Yii::t('app', 'Значение по умолчанию'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistories()
    {
        return $this->hasMany(History::className(), ['item_id' => 'id'])->inverseOf('item');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBoard()
    {
        return $this->hasOne(Board::className(), ['id' => 'board_id'])->inverseOf('items');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWidget()
    {
        return $this->hasOne(ItemWidget::className(), ['item_id' => 'id'])->inverseOf('item');
    }

    /**
     * @inheritdoc
     * @return ItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemQuery(get_called_class());
    }

    /**
     * @return array
     */
    public static function getTypesArray()
    {
        return [
            self::TYPE_SWITCH => 'Переключатель',
            self::TYPE_VARIABLE => 'Переменная',
            self::TYPE_VARIABLE_BOOLEAN => 'Переменная boolean',
            self::TYPE_VARIABLE_BOOLEAN_DOOR => 'Переменная boolean дверь',
            self::TYPE_VARIABLE_TEMPERATURE => 'Переменная температура',
            self::TYPE_VARIABLE_HUMIDITY => 'Переменная влажность',
            self::TYPE_VARIABLE_LIGHT => 'Переменная освещенность',
            self::TYPE_RGB => 'RGB',
            self::TYPE_PLANT => 'Растение',
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
     * @param bool $prependId
     * @return array
     */
    public static function getList($prependId = false)
    {
        /** @var self[] $models */
        $models = self::find()->all();
        $result = [];

        if (!$prependId) {
            return ArrayHelper::map($models, 'id', 'name');
        }

        foreach ($models as $model) {
            $title = '';

            if ($prependId) {
                $title .= '#' . $model->id . ' ';
            }

            $title .= $model->name;

            $result[$model->id] = $title;
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getRGBModesArray()
    {
        return [
            self::RGB_MODE_STATIC,
            self::RGB_MODE_WAVE,
            self::RGB_MODE_FADE,
        ];
    }

    public function getDefaultNAValue()
    {
        switch ($this->type) {
            case self::TYPE_RGB:
                return [
                    'mode' => 'static',
                    'red' => 0,
                    'green' => 0,
                    'blue' => 0,
                    'fade_time' => Yii::$app->params['items']['rgb']['fade-time'],
                ];
            default:
                return 'N/A';
        }
    }
}
