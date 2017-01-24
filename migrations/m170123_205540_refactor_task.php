<?php

use yii\db\Migration;

class m170123_205540_refactor_task extends Migration
{
    public function safeUp()
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

        $this->addColumn('task', 'active', $this->boolean()->after('id')->defaultValue(true));
    }

    public function safeDown()
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
}
