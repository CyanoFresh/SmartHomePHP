<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[ItemWidget]].
 *
 * @see ItemWidget
 */
class ItemWidgetQuery extends ActiveQuery
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
    public function switches()
    {
        return $this->andWhere(['type' => ItemWidget::TYPE_SWITCH]);
    }

    /**
     * @return $this
     */
    public function variables()
    {
        return $this->andWhere(['type' => ItemWidget::TYPE_VARIABLE]);
    }

    /**
     * @return $this
     */
    public function rgb()
    {
        return $this->andWhere(['type' => ItemWidget::TYPE_RGB]);
    }

    /**
     * @return $this
     */
    public function plant()
    {
        return $this->andWhere(['type' => ItemWidget::TYPE_PLANT]);
    }

    /**
     * @inheritdoc
     * @return ItemWidget[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ItemWidget|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
