<?php

use yii\db\Migration;

/**
 * Handles the creation of table `event_trigger`.
 * Has foreign keys to the tables:
 *
 * - `event`
 * - `trigger`
 */
class m170123_204850_create_junction_table_for_event_and_trigger_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('event_trigger', [
            'event_id' => $this->integer(),
            'trigger_id' => $this->integer(),
            'PRIMARY KEY(event_id, trigger_id)',
        ]);

        // creates index for column `event_id`
        $this->createIndex(
            'idx-event_trigger-event_id',
            'event_trigger',
            'event_id'
        );

        // add foreign key for table `event`
        $this->addForeignKey(
            'fk-event_trigger-event_id',
            'event_trigger',
            'event_id',
            'event',
            'id',
            'CASCADE'
        );

        // creates index for column `trigger_id`
        $this->createIndex(
            'idx-event_trigger-trigger_id',
            'event_trigger',
            'trigger_id'
        );

        // add foreign key for table `trigger`
        $this->addForeignKey(
            'fk-event_trigger-trigger_id',
            'event_trigger',
            'trigger_id',
            'trigger',
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
            'fk-event_trigger-event_id',
            'event_trigger'
        );

        // drops index for column `event_id`
        $this->dropIndex(
            'idx-event_trigger-event_id',
            'event_trigger'
        );

        // drops foreign key for table `trigger`
        $this->dropForeignKey(
            'fk-event_trigger-trigger_id',
            'event_trigger'
        );

        // drops index for column `trigger_id`
        $this->dropIndex(
            'idx-event_trigger-trigger_id',
            'event_trigger'
        );

        $this->dropTable('event_trigger');
    }
}
