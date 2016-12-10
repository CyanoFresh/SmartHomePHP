<?php

use yii\db\Migration;

class m161210_183620_add_default_value_to_item_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('item', 'default_value', $this->string()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('item', 'default_value');
    }
}
