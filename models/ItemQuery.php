<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Item]].
 *
 * @see Item
 */
class ItemQuery extends \yii\db\ActiveQuery
{
    public function variables()
    {
        return $this->andWhere([
            'type' => [
                Item::TYPE_VARIABLE,
                Item::TYPE_VARIABLE_BOOLEAN,
                Item::TYPE_VARIABLE_BOOLEAN_DOOR,
                Item::TYPE_VARIABLE_TEMPERATURE,
                Item::TYPE_VARIABLE_HUMIDITY,
            ],
        ]);
    }

    public function switches()
    {
        return $this->andWhere([
            'type' => [
                Item::TYPE_SWITCH,
            ],
        ]);
    }

    public function rgb()
    {
        return $this->andWhere([
            'type' => [
                Item::TYPE_RGB,
            ],
        ]);
    }

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
