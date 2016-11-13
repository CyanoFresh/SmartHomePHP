<?php

use yii\db\Migration;

class m161113_011851_history_update extends Migration
{
    public function safeUp()
    {
        $this->addColumn('history', 'type', $this->smallInteger()->after('id')->notNull()->defaultValue(10));
        $this->addColumn('history', 'event_id', $this->integer()->after('type'));
        $this->addColumn('history', 'board_id', $this->integer()->after('event_id'));
        $this->addColumn('history', 'user_id', $this->integer()->after('board_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('history', 'type');
        $this->dropColumn('history', 'event_id');
        $this->dropColumn('history', 'board_id');
        $this->dropColumn('history', 'user_id');
    }
}
