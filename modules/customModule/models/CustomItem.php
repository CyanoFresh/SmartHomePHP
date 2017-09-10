<?php

namespace app\modules\customModule\models;

use app\modules\customModule\models\query\CustomItemQuery;
use app\modules\server\models\BaseItem;

class CustomItem extends BaseItem
{
    const TYPE = 'custom';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'custom_item';
    }
}
