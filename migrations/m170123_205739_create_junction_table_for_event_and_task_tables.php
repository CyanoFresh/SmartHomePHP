<?php

use yii\db\Migration;

/**
 * Handles the creation of table `event_task`.
 * Has foreign keys to the tables:
 *
 * - `event`
 * - `task`
 */
class m170123_205739_create_junction_table_for_event_and_task_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('event_task', [
            'event_id' => $this->integer(),
            'task_id' => $this->integer(),
            'PRIMARY KEY(event_id, task_id)',
        ]);

        // creates index for column `event_id`
        $this->createIndex(
            'idx-event_task-event_id',
            'event_task',
            'event_id'
        );

        // add foreign key for table `event`
        $this->addForeignKey(
            'fk-event_task-event_id',
            'event_task',
            'event_id',
            'event',
            'id',
            'CASCADE'
        );

        // creates index for column `task_id`
        $this->createIndex(
            'idx-event_task-task_id',
            'event_task',
            'task_id'
        );

        // add foreign key for table `task`
        $this->addForeignKey(
            'fk-event_task-task_id',
            'event_task',
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
        // drops foreign key for table `event`
        $this->dropForeignKey(
            'fk-event_task-event_id',
            'event_task'
        );

        // drops index for column `event_id`
        $this->dropIndex(
            'idx-event_task-event_id',
            'event_task'
        );

        // drops foreign key for table `task`
        $this->dropForeignKey(
            'fk-event_task-task_id',
            'event_task'
        );

        // drops index for column `task_id`
        $this->dropIndex(
            'idx-event_task-task_id',
            'event_task'
        );

        $this->dropTable('event_task');
    }
}
