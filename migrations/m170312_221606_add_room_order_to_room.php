<?php

use yii\db\Migration;

class m170312_221606_add_room_order_to_room extends Migration
{
    public function safeUp()
    {
        $this->addColumn('room', 'sort_order', $this->integer()->defaultValue(0));
        $this->dropColumn('room', 'bg');
    }

    public function safeDown()
    {
        $this->dropColumn('room', 'sort_order');
        $this->addColumn('room', 'bg', $this->string());
    }
}
