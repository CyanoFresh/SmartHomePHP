<?php

use yii\db\Migration;

class m170124_174345_fix_history_value extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('history', 'value', $this->string()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->alterColumn('history', 'value', $this->string()->notNull());
    }
}
