<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Item]].
 *
 * @see Item
 */
class ItemQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Item[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Item|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
