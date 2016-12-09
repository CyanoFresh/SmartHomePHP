<?php

use yii\db\Migration;

class m161208_212800_event_refactor extends Migration
{
    public function safeUp()
    {
        $this->renameTable('event', 'trigger');
        $this->dropColumn('trigger', 'task_id');

        $this->addColumn('task', 'type', $this->integer()->notNull()->after('id'));
        $this->addColumn('task', 'item_id', $this->integer()->after('type'));
        $this->addColumn('task', 'item_value', $this->string()->after('item_id'));

        $this->dropTable('task_action');
    }

    public function safeDown()
    {
        $this->renameTable('trigger', 'event');
        $this->addColumn('trigger', 'task_id', $this->integer()->notNull()->after('id'));

        $this->dropColumn('task', 'type');
        $this->dropColumn('task', 'item_id');
        $this->dropColumn('task', 'item_value');
    }
}
