<?php

use yii\db\Migration;

class m161028_190404_pin_null extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('item', 'pin', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('item', 'pin', $this->integer()->notNull());
    }
}
