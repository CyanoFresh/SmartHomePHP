<?php

use yii\db\Migration;

class m161111_185731_add_active_to_item extends Migration
{
    public function safeUp()
    {
        $this->addColumn('item', 'active', $this->boolean()->after('id')->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn('item', 'active');
    }
}
