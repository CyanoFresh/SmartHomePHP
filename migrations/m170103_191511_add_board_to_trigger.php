<?php

use yii\db\Migration;

class m170103_191511_add_board_to_trigger extends Migration
{
    public function safeUp()
    {
        $this->addColumn('trigger', 'trig_board_id', $this->integer()->after('trig_item_value'));
        $this->addColumn('trigger', 'trig_connection_value', $this->string()->after('trig_board_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('trigger', 'trig_board_id');
        $this->dropColumn('trigger', 'trig_connection_value');
    }
}
