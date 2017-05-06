<?php

use yii\db\Migration;

class m170303_164850_fix_items extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('item', 'active');
        $this->dropColumn('item', 'room_id');
        $this->dropColumn('item', 'bg');
        $this->dropColumn('item', 'class');
        $this->dropColumn('item', 'icon');
        $this->dropColumn('item', 'sort_order');
    }

    public function safeDown()
    {
        $this->addColumn('item', 'active', $this->boolean()->defaultValue(true));
        $this->addColumn('item', 'room_id', $this->integer()->notNull());
        $this->addColumn('item', 'bg', $this->string());
        $this->addColumn('item', 'class', $this->string());
        $this->addColumn('item', 'icon', $this->string()->notNull());
        $this->addColumn('item', 'sort_order', $this->integer()->defaultValue(0));
    }
}
