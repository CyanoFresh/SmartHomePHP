<?php

use yii\db\Migration;

class m170123_204012_create_event extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('event', [
            'id' => $this->primaryKey(),
            'active' => $this->boolean()->defaultValue(true),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'last_triggered_at' => $this->integer(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('event');
    }
}
