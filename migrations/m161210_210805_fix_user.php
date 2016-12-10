<?php

use yii\db\Migration;

class m161210_210805_fix_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('user', 'auth_token', $this->string()->defaultValue(null)->after('auth_key'));
    }

    public function safeDown()
    {
        $this->dropColumn('user', 'auth_token');
    }
}
