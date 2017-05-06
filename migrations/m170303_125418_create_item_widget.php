<?php

use yii\db\Migration;

class m170303_125418_create_item_widget extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('item_widget', [
            'id' => $this->primaryKey(),
            'active' => $this->boolean()->defaultValue(true),
            'type' => $this->smallInteger()->notNull(),
            'value_type' => $this->smallInteger(),
            'sort_order' => $this->integer()->defaultValue(0),
            'item_id' => $this->integer()->notNull(),
            'room_id' => $this->integer(),
            'name' => $this->string()->notNull(),
            'html_class' => $this->string(),
            'icon' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-item_widget-item_id', 'item_widget', 'item_id');
        $this->addForeignKey('fk-item_widget-item_id', 'item_widget', 'item_id', 'item', 'id', 'CASCADE');

        $this->createIndex('idx-item_widget-room_id', 'item_widget', 'room_id');
        $this->addForeignKey('fk-item_widget-room_id', 'item_widget', 'room_id', 'room', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-item_widget-item_id', 'item_widget');
        $this->dropIndex('idx-item_widget-item_id', 'item_widget');

        $this->dropForeignKey('fk-item_widget-room_id', 'item_widget');
        $this->dropIndex('idx-item_widget-room_id', 'item_widget');

        $this->dropTable('item_widget');
    }
}
