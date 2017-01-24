<?php

namespace app\models;

use voskobovich\linker\LinkerBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "task".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $item_id
 * @property string $item_value
 * @property string $text
 * @property string $name
 *
 * @property Item $item
 * @property Event[] $event
 */
class Task extends ActiveRecord
{
    const TYPE_ITEM_VALUE = 10;
    const TYPE_NOTIFICATION_TELEGRAM = 20;

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
            [['text'], 'string'],
            [['event_ids'], 'each', 'rule' => ['integer']],
            [['type'], 'in', 'range' => self::getTypesArray()],
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
            'event_ids' => 'События',
            'name' => 'Название',
            'text' => 'Текст',
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
                    'event_ids' => 'events',
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
            self::TYPE_NOTIFICATION_TELEGRAM => 'Сообщение Telegram',
        ];
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
     * @return \yii\db\ActiveQuery
     */
    public function getEvents()
    {
        return $this->hasMany(Event::className(), ['id' => 'event_id'])
            ->viaTable('event_task', ['task_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }

    /**
     * @return bool
     */
    public function sendNotificationTelegram()
    {
        $url = 'https://api.telegram.org/bot' . Yii::$app->params['telegramBotApiKey']
            . '/sendMessage?chat_id=' . Yii::$app->params['telegramBotChatId']
            . '&text=' . $this->text;

        $result = file_get_contents($url);
        $data = Json::decode($result);

        return (isset($data['ok']) and $data['ok']);
    }
}
