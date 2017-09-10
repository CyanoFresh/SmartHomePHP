<?php

namespace app\modules\server\models;

use app\models\Device;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;

/**
 * Class BaseItem
 *
 * @package app\modules\server\models
 *
 * @property integer $id
 * @property integer $device_id
 * @property string $type
 * @property string $name
 * @property string $title
 *
 * @property Device $device
 */
abstract class BaseItem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'base';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->type = static::getType();

        parent::init();
    }

    /**
     * @throws NotSupportedException
     */
    public static function find()
    {
        throw new NotSupportedException('Item query is not defined');
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->type = static::getType();

        return parent::beforeSave($insert);
    }

}
