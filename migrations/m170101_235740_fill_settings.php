<?php

use yii\db\Migration;

class m170101_235740_fill_settings extends Migration
{
    public function safeUp()
    {
        $this->insert('setting', [
            'key' => 'log.user_connection',
            'title' => 'Логирование подключений пользователей',
            'value' => 0,
        ]);

        $this->insert('setting', [
            'key' => 'log.board_connection',
            'title' => 'Логирование подключений плат',
            'value' => 1,
        ]);
    }

    public function safeDown()
    {
        $this->delete('setting', [
            'key' => [
                'log.board_connection',
                'log.user_connection',
            ],
        ]);
    }
}
