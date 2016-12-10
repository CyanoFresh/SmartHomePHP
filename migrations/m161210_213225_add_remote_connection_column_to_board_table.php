<?php

use yii\db\Migration;

/**
 * Handles adding remote_connection to table `board`.
 */
class m161210_213225_add_remote_connection_column_to_board_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('board', 'remote_connection', $this->boolean()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('board', 'remote_connection');
    }
}
