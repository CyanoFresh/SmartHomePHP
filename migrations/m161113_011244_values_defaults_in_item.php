<?php

use yii\db\Migration;

class m161113_011244_values_defaults_in_item extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('item', 'update_interval', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->alterColumn('item', 'update_interval', $this->integer()->notNull());
    }
}
