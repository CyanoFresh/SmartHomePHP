<?php

namespace app\modules\server\models\query;

use yii\db\ActiveQuery;

abstract class BaseItemQuery extends ActiveQuery
{
    public $type;

    public function prepare($builder)
    {
        if ($this->type !== null) {
            $this->andWhere(['type' => $this->type]);
        }

        return parent::prepare($builder);
    }
}
