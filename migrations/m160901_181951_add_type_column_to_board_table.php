<?php

use yii\db\Migration;

/**
 * Handles adding type to table `board`.
 */
class m160901_181951_add_type_column_to_board_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('board', 'type', $this->smallInteger()->defaultValue(10)->after('id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('board', 'type');
    }
}
