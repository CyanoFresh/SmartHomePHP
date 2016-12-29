<?php

use yii\db\Migration;

class m161229_202800_fix_history_item_id extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('history', 'item_id', $this->integer());
    }

    public function safeDown()
    {
        $this->alterColumn('history', 'item_id', $this->integer()->notNull());
    }
}
