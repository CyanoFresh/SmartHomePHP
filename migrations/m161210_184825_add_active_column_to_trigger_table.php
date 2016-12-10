<?php

use yii\db\Migration;

/**
 * Handles adding active to table `trigger`.
 */
class m161210_184825_add_active_column_to_trigger_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('trigger', 'active', $this->boolean()->notNull()->defaultValue(true)->after('id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('trigger', 'active');
    }
}
