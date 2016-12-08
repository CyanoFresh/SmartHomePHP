<?php

use yii\db\Migration;

class m161208_192446_add_enable_log_item extends Migration
{
    public function safeUp()
    {
        $this->addColumn('item', 'enable_log', $this->boolean()->defaultValue(true)->after('active'));
    }

    public function safeDown()
    {
        $this->dropColumn('item', 'enable_log');
    }
}
