<?php

use yii\db\Migration;

class m170901_134923_create_device extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('device', [
            'id' => $this->primaryKey(),
            'type' => $this->string(100)->notNull(),
            'room_id' => $this->integer(),
            'name' => $this->string(100)->notNull(),
            'title' => $this->string()->notNull(),
            'key' => $this->string()->notNull(),
            'allow_remote_connection' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);

        $this->createIndex('idx-device-type', 'device', 'type');
        $this->createIndex('idx-device-room_id', 'device', 'room_id');
        $this->createIndex('idx-device-key', 'device', 'key', true);

        $this->addForeignKey(
            'fk-device-room_id',
            'device',
            'room_id',
            'room',
            'id',
            'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-device-room_id',
            'device'
        );

        $this->dropIndex('idx-device-type', 'device');
        $this->dropIndex('idx-device-room_id', 'device');
        $this->dropIndex('idx-device-key', 'device');

        $this->dropTable('device');
    }
}
