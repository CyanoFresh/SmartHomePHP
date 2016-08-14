<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[History]].
 *
 * @see History
 */
class HistoryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return History[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return History|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
