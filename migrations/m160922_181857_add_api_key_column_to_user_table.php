<?php

use yii\db\Migration;

/**
 * Handles adding api_key to table `user`.
 */
class m160922_181857_add_api_key_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('user', 'api_key', $this->string()->notNull()->after('auth_key'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'api_key');
    }
}
