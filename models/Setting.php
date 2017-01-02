<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "setting".
 *
 * @property integer $id
 * @property string $key
 * @property string $title
 * @property string $value
 */
class Setting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'title'], 'required'],
            [['key', 'title', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Ключ',
            'title' => 'Название',
            'value' => 'Значение',
        ];
    }

    /**
     * @param string|int $key
     * @return null|string
     */
    public static function getValueByKey($key)
    {
        /** @var self $model */
        $model = self::find()->where(['key' => $key])->select('value')->one();

        if (!$model) {
            return null;
        }

        return $model->value;
    }
}
