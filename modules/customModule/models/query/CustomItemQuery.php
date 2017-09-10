<?php

namespace app\modules\customModule\models\query;

use app\modules\customModule\models\CustomItem;
use app\modules\server\models\query\BaseItemQuery;
use yii\db\ActiveQuery;

/**
 * @see CustomItem
 */
class CustomItemQuery extends BaseItemQuery
{
    /**
     * @inheritdoc
     * @return CustomItem[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CustomItem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
