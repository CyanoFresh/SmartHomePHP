<?php

use yii\db\Migration;

class m170727_174033_add_indexes extends Migration
{
    public function safeUp()
    {
        $this->createIndex('idx-board-type', 'board', 'type');
        $this->createIndex('idx-board-secret', 'board', 'secret');
        $this->createIndex('idx-event-active', 'event', 'active');
        $this->createIndex('idx-history-type', 'history', 'type');
        $this->createIndex('idx-history-event_id', 'history', 'event_id');
        $this->createIndex('idx-history-board_id', 'history', 'board_id');
        $this->createIndex('idx-history-user_id', 'history', 'user_id');
        $this->createIndex('idx-history-item_id', 'history', 'item_id');
        $this->createIndex('idx-history-commited_at', 'history', 'commited_at');
        $this->createIndex('idx-item-board_id', 'item', 'board_id');
        $this->createIndex('idx-item-pin', 'item', 'pin');
        $this->createIndex('idx-item-type', 'item', 'type');
        $this->createIndex('idx-item_widget-active', 'item_widget', 'active');
        $this->createIndex('idx-item_widget-type', 'item_widget', 'type');
        $this->createIndex('idx-item_widget-value_type', 'item_widget', 'value_type');
        $this->createIndex('idx-item_widget-sort_order', 'item_widget', 'sort_order');
        $this->createIndex('idx-room-sort_order', 'room', 'sort_order');
        $this->createIndex('idx-setting-key', 'setting', 'key');
        $this->createIndex('idx-task-active', 'task', 'active');
        $this->createIndex('idx-task-type', 'task', 'type');
        $this->createIndex('idx-task-item_id', 'task', 'item_id');
        $this->createIndex('idx-trigger-active', 'trigger', 'active');
        $this->createIndex('idx-trigger-type', 'trigger', 'type');
        $this->createIndex('idx-user-auth_key', 'user', 'auth_key');
        $this->createIndex('idx-user-auth_token', 'user', 'auth_token');
        $this->createIndex('idx-user-status', 'user', 'status');
        $this->createIndex('idx-user-group', 'user', 'group');
        $this->createIndex('idx-user-api_key', 'user', 'api_key');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-board-type', 'board');
        $this->dropIndex('idx-board-secret', 'board');
        $this->dropIndex('idx-event-active', 'event');
        $this->dropIndex('idx-event-active', 'event');
        $this->dropIndex('idx-history-type', 'history');
        $this->dropIndex('idx-history-event_id', 'history');
        $this->dropIndex('idx-history-board_id', 'history');
        $this->dropIndex('idx-history-user_id', 'history');
        $this->dropIndex('idx-history-item_id', 'history');
        $this->dropIndex('idx-history-commited_at', 'history');
        $this->dropIndex('idx-item-board_id', 'item');
        $this->dropIndex('idx-item-pin', 'item');
        $this->dropIndex('idx-item-type', 'item');
        $this->dropIndex('idx-item_widget-active', 'item_widget');
        $this->dropIndex('idx-item_widget-type', 'item_widget');
        $this->dropIndex('idx-item_widget-value_type', 'item_widget');
        $this->dropIndex('idx-item_widget-sort_order', 'item_widget');
        $this->dropIndex('idx-room-sort_order', 'room');
        $this->dropIndex('idx-setting-key', 'setting');
        $this->dropIndex('idx-task-active', 'task');
        $this->dropIndex('idx-task-type', 'task');
        $this->dropIndex('idx-task-item_id', 'task');
        $this->dropIndex('idx-trigger-active', 'trigger');
        $this->dropIndex('idx-trigger-type', 'trigger');
        $this->dropIndex('idx-user-auth_key', 'user');
        $this->dropIndex('idx-user-auth_token', 'user');
        $this->dropIndex('idx-user-status', 'user');
        $this->dropIndex('idx-user-group', 'user');
        $this->dropIndex('idx-user-api_key', 'user');
    }
}
