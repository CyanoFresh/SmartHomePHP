<?php

use yii\db\Migration;

class m161126_184646_create_events extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('event', [
            'id' => $this->primaryKey(),
            'type' => $this->smallInteger()->notNull(),
            'trig_date' => $this->string(),
            'trig_time' => $this->string(),
            'trig_time_wdays' => $this->string(),
            'trig_item_id' => $this->integer(),
            'trig_item_value' => $this->string(),
            'task_id' => $this->integer(),
            'name' => $this->string()->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('event');
    }
}
