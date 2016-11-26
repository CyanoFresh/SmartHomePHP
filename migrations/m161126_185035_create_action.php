<?php

use yii\db\Migration;

class m161126_185035_create_action extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('task_action', [
            'id' => $this->primaryKey(),
            'type' => $this->smallInteger()->notNull(),
            'item_id' => $this->integer(),
            'item_value' => $this->string(),
            'task_id' => $this->integer(),
            'name' => $this->string()->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('task_action');
    }
}
