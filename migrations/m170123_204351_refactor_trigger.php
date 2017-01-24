<?php

use yii\db\Migration;

class m170123_204351_refactor_trigger extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('trigger', 'trig_date', 'date');
        $this->renameColumn('trigger', 'trig_time', 'time');
        $this->renameColumn('trigger', 'trig_time_wdays', 'weekdays');
        $this->renameColumn('trigger', 'trig_item_id', 'item_id');
        $this->renameColumn('trigger', 'trig_item_value', 'item_value');
        $this->renameColumn('trigger', 'trig_board_id', 'board_id');
        $this->renameColumn('trigger', 'trig_connection_value', 'connection_value');

        $this->createIndex('idx-trigger-item_id', 'trigger', 'item_id');
        $this->addForeignKey('fk-trigger-item_id', 'trigger', 'item_id', 'item', 'id', 'CASCADE');

        $this->createIndex('idx-trigger-board_id', 'trigger', 'board_id');
        $this->addForeignKey('fk-trigger-board_id', 'trigger', 'board_id', 'board', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-trigger-item_id', 'trigger');
        $this->dropIndex('idx-trigger-item_id', 'trigger');
        $this->dropForeignKey('fk-trigger-board_id', 'trigger');
        $this->dropIndex('idx-trigger-board_id', 'trigger');

        $this->renameColumn('trigger', 'date', 'trig_date');
        $this->renameColumn('trigger', 'time', 'trig_time');
        $this->renameColumn('trigger', 'weekdays', 'trig_time_wdays');
        $this->renameColumn('trigger', 'item_id', 'trig_item_id');
        $this->renameColumn('trigger', 'item_value', 'trig_item_value');
        $this->renameColumn('trigger', 'board_id', 'trig_board_id');
        $this->renameColumn('trigger', 'connection_value', 'trig_connection_value');
    }
}
