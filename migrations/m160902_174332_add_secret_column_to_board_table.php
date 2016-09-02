<?php

use yii\db\Migration;

/**
 * Handles adding secret to table `board`.
 */
class m160902_174332_add_secret_column_to_board_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('board', 'secret', $this->string()->after('name'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('board', 'secret');
    }
}
