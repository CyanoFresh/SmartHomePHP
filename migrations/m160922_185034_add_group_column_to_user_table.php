<?php

use yii\db\Migration;

/**
 * Handles adding group to table `user`.
 */
class m160922_185034_add_group_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('user', 'group', $this->integer()->defaultValue(20)->after('id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('user', 'group');
    }
}
