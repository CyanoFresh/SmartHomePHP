<?php

use yii\db\Migration;

class m170316_163700_add_user_fields extends Migration
{
    public function safeUp()
    {
        $this->addColumn('user', 'name', $this->string()->after('email'));
        $this->addColumn('user', 'avatar', $this->string()->after('name'));
        $this->addColumn('user', 'room_id', $this->integer()->after('email'));

        $this->createIndex('idx-user-room_id', 'user', 'room_id');
        $this->addForeignKey('fk-user-room_id', 'user', 'room_id', 'room', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-user-room_id', 'user');
        $this->dropIndex('idx-user-room_id', 'user');

        $this->dropColumn('user', 'name');
        $this->dropColumn('user', 'avatar');
        $this->dropColumn('user', 'room_id');
    }
}
