<?php

use yii\db\Migration;

/**
 * Handles adding pin to table `item`.
 */
class m160902_180312_add_pin_column_to_item_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('item', 'pin', $this->integer()->after('board_id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('item', 'pin');
    }
}
