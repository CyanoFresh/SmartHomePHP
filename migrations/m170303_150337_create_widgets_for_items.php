<?php

use app\models\Item;
use app\models\ItemWidget;
use yii\db\Migration;

class m170303_150337_create_widgets_for_items extends Migration
{
    public function safeUp()
    {
        foreach (Item::find()->all() as $item) {
            $itemWidget = new ItemWidget();
            $itemWidget->active = true;
            $itemWidget->item_id = $item->id;
            $itemWidget->room_id = $item->room_id;
            $itemWidget->name = $item->name;
            $itemWidget->html_class = $item->class;
            $itemWidget->icon = 'fa-' . $item->icon;

            $itemWidget->type = $item->type;

            if ($item->type == 21 or $item->type == 22 or $item->type == 25 or $item->type == 26) {
                $itemWidget->type = 20;

                if ($item->type == 21) {
                    $itemWidget->value_type = ItemWidget::VALUE_TYPE_CELSIUS;
                } elseif ($item->type == 22) {
                    $itemWidget->value_type = ItemWidget::VALUE_TYPE_PERCENT;
                } elseif ($item->type == 25) {
                    $itemWidget->value_type = ItemWidget::VALUE_TYPE_BOOLEAN;
                } elseif ($item->type == 26) {
                    $itemWidget->value_type = ItemWidget::VALUE_TYPE_DOOR;
                }
            }

            $itemWidget->save();
        }
    }

    public function safeDown()
    {
        return 0;
    }
}
