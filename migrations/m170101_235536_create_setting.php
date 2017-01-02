<?php

use yii\db\Migration;

class m170101_235536_create_setting extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('setting', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'value' => $this->string(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('setting');
    }
}
