<?php

use yii\db\Migration;

class m161231_183339_add_text_to_task extends Migration
{
    public function safeUp()
    {
        $this->addColumn('task', 'text', $this->text());
    }

    public function safeDown()
    {
        $this->dropColumn('task', 'text');
    }
}
