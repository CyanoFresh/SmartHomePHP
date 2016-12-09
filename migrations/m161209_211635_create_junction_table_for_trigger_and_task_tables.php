<?php

use yii\db\Migration;

/**
 * Handles the creation of table `trigger_task`.
 * Has foreign keys to the tables:
 *
 * - `trigger`
 * - `task`
 */
class m161209_211635_create_junction_table_for_trigger_and_task_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('trigger_task', [
            'trigger_id' => $this->integer(),
            'task_id' => $this->integer(),
            'PRIMARY KEY(trigger_id, task_id)',
        ]);

        // creates index for column `trigger_id`
        $this->createIndex(
            'idx-trigger_task-trigger_id',
            'trigger_task',
            'trigger_id'
        );

        // add foreign key for table `trigger`
        $this->addForeignKey(
            'fk-trigger_task-trigger_id',
            'trigger_task',
            'trigger_id',
            'trigger',
            'id',
            'CASCADE'
        );

        // creates index for column `task_id`
        $this->createIndex(
            'idx-trigger_task-task_id',
            'trigger_task',
            'task_id'
        );

        // add foreign key for table `task`
        $this->addForeignKey(
            'fk-trigger_task-task_id',
            'trigger_task',
            'task_id',
            'task',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `trigger`
        $this->dropForeignKey(
            'fk-trigger_task-trigger_id',
            'trigger_task'
        );

        // drops index for column `trigger_id`
        $this->dropIndex(
            'idx-trigger_task-trigger_id',
            'trigger_task'
        );

        // drops foreign key for table `task`
        $this->dropForeignKey(
            'fk-trigger_task-task_id',
            'trigger_task'
        );

        // drops index for column `task_id`
        $this->dropIndex(
            'idx-trigger_task-task_id',
            'trigger_task'
        );

        $this->dropTable('trigger_task');
    }
}
