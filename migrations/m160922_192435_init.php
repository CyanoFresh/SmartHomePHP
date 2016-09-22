<?php

use app\models\User;
use yii\db\Migration;

class m160922_192435_init extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'group' => $this->smallInteger()->notNull()->defaultValue(20),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'api_key' => $this->string(32),
            'password_hash' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        // Register admin account
        $user = new User([
            'scenario' => 'create',
        ]);

        $user->username = 'admin';
        $user->email = 'admin@domain.com';
        $user->setPassword('admin');
        $user->generateAuthKey();

        if (!$user->save()) {
            echo 'Cannot create admin account' . PHP_EOL;
        }

        $this->createTable('board', [
            'id' => $this->primaryKey(),
            'type' => $this->smallInteger()->notNull(),
            'name' => $this->string()->notNull(),
            'secret' => $this->string()->notNull(),
            'baseUrl' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createTable('history', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'item_id' => $this->integer()->notNull(),
            'commited_at' => $this->integer()->notNull(),
            'value' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createTable('item', [
            'id' => $this->primaryKey(),
            'board_id' => $this->integer()->notNull(),
            'room_id' => $this->integer()->notNull(),
            'pin' => $this->integer()->notNull(),
            'type' => $this->integer()->notNull(),
            'update_interval' => $this->integer()->defaultValue(0),
            'save_history_interval' => $this->integer()->defaultValue(0),
            'name' => $this->string()->notNull(),
            'bg' => $this->string()->notNull(),
            'class' => $this->string(),
            'icon' => $this->string(),
            'url' => $this->string(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createTable('room', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'bg' => $this->string(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('user');
        $this->dropTable('board');
        $this->dropTable('room');
        $this->dropTable('item');
        $this->dropTable('history');
    }
}
