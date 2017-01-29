<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Item]].
 *
 * @see Item
 */
class ItemQuery extends \yii\db\ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(['active' => true]);
    }

    /**
     * @return $this
     */
    public function variables()
    {
        return $this->andWhere([
            'type' => [
                Item::TYPE_VARIABLE,
                Item::TYPE_VARIABLE_BOOLEAN,
                Item::TYPE_VARIABLE_BOOLEAN_DOOR,
                Item::TYPE_VARIABLE_TEMPERATURE,
                Item::TYPE_VARIABLE_HUMIDITY,
                Item::TYPE_LIGHT_LEVEL,
            ],
        ]);
    }

    /**
     * @return $this
     */
    public function switches()
    {
        return $this->andWhere([
            'type' => [
                Item::TYPE_SWITCH,
            ],
        ]);
    }

    /**
     * @return $this
     */
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
